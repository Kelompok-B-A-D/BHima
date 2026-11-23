<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintWorkflowPbi16Pbi17Test extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'verifikator'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    private function complaint(string $key, int $assigned, array $overrides = []): void
    {
        DB::table('dashboard_records')->insert([
            'collection' => 'complaints',
            'record_key' => $key,
            'assigned_to' => $assigned,
            'payload' => json_encode([
                'id' => $key,
                'date' => now()->toDateString(),
                'category' => 'Pelayanan Publik',
                'reporter' => 'Anonim',
                'summary' => 'Ringkasan pengaduan',
                'status' => 'Baru',
                'priority' => 'Normal',
                'officer' => null,
                'chronology' => 'Kronologi pengaduan yang cukup panjang.',
                'reported' => 'Unit pelayanan',
                'location' => 'Kantor desa',
                'incidentDate' => now()->toDateString(),
                'attachments' => [],
                'ai' => 'Hasil pre-screening',
                'references' => ['UU No. 1 Tahun 2026'],
                'notes' => [],
                'history' => [],
                ...$overrides,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function payload(string $key): array
    {
        return json_decode(
            DB::table('dashboard_records')->where('record_key', $key)->value('payload'),
            true
        );
    }

    public function test_verifier_records_completeness_request_rejection_and_disposition(): void
    {
        Storage::fake('local');
        $verifier = $this->user();
        $this->complaint('BHM-2026-0010', $verifier->id);
        $this->complaint('BHM-2026-0010R', $verifier->id);
        $this->actingAs($verifier);

        $this->postJson('/internal/data/complaints', [
            'id' => 'BHM-2026-0010',
            'action' => 'complete',
            'note' => 'Lampiran belum lengkap.',
            'requested_documents' => "KTP\nBukti pembayaran",
        ])->assertOk();

        $payload = $this->payload('BHM-2026-0010');
        $this->assertSame('Menunggu Kelengkapan', $payload['status']);
        $this->assertSame(['KTP', 'Bukti pembayaran'], $payload['completeness_request']['documents']);
        $this->assertSame('Lampiran belum lengkap.', $payload['completeness_request']['note']);
        $this->assertSame($verifier->name, $payload['completeness_request']['requested_by']);
        $this->assertNull($payload['completeness_request']['fulfilled_at']);

        $this->postJson('/internal/data/complaints', [
            'id' => 'BHM-2026-0010',
            'action' => 'verify',
            'note' => 'Kelengkapan diterima.',
        ])->assertOk();

        $this->postJson('/internal/data/complaints', [
            'id' => 'BHM-2026-0010',
            'action' => 'dispatch',
            'note' => 'Diteruskan ke inspektorat.',
            'agency' => 'Inspektorat Daerah',
        ])->assertOk();

        $payload = $this->payload('BHM-2026-0010');
        $this->assertSame('Dalam Proses', $payload['status']);
        $this->assertSame('Inspektorat Daerah', $payload['agency']);
        $this->assertCount(1, $payload['dispositions']);
        $this->assertSame('Inspektorat Daerah', $payload['dispositions'][0]['agency']);
        $this->assertSame('Diteruskan ke inspektorat.', $payload['dispositions'][0]['note']);
        $this->assertSame($verifier->name, $payload['dispositions'][0]['by']);

        $this->post('/internal/data/complaints', [
            'id' => 'BHM-2026-0010',
            'action' => 'status',
            'note' => 'Tindak lanjut selesai dilaksanakan.',
            'status' => 'Selesai',
            'follow_up_summary' => 'Instansi sudah menindaklanjuti dan memberikan sanksi administratif.',
            'follow_up_file' => UploadedFile::fake()->createWithContent('hasil.pdf', '%PDF-1.4'),
        ], ['Accept' => 'application/json'])->assertOk();

        $payload = $this->payload('BHM-2026-0010');
        $this->assertSame('Selesai', $payload['status']);
        $this->assertSame(
            'Instansi sudah menindaklanjuti dan memberikan sanksi administratif.',
            $payload['follow_up']['summary']
        );
        $this->assertSame('hasil.pdf', $payload['follow_up']['attachment']['name']);
        $this->assertStringStartsWith('complaints/BHM-2026-0010/tindak-lanjut/', $payload['follow_up']['attachment']['path']);
        Storage::disk('local')->assertExists($payload['follow_up']['attachment']['path']);

        $this->postJson('/internal/data/complaints', [
            'id' => 'BHM-2026-0010R',
            'action' => 'reject',
            'note' => 'Laporan berada di luar kewenangan.',
        ])->assertOk();

        $rejectedPayload = $this->payload('BHM-2026-0010R');
        $this->assertSame('Ditolak', $rejectedPayload['status']);
        $this->assertSame('Laporan berada di luar kewenangan.', $rejectedPayload['rejection_reason']);
    }

    public function test_verifier_downloads_evidence_only_for_assigned_complaint(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('complaints/BHM-2026-0011/bukti.pdf', '%PDF-1.4');
        Storage::disk('local')->put('complaints/BHM-2026-0011/tindak-lanjut/hasil.pdf', '%PDF-1.4');
        Storage::disk('local')->put('complaints/BHM-2026-OTHER/rahasia.pdf', '%PDF-1.4');

        $verifier = $this->user();
        $other = $this->user();
        $admin = $this->user('admin');

        $this->complaint('BHM-2026-0011', $verifier->id, [
            'attachments' => [
                [
                    'name' => 'bukti.pdf',
                    'path' => 'complaints/BHM-2026-0011/bukti.pdf',
                    'mime' => 'application/pdf',
                    'size' => 8,
                ],
                [
                    'name' => 'traversal.pdf',
                    'path' => 'complaints/BHM-2026-0011/../BHM-2026-OTHER/rahasia.pdf',
                    'mime' => 'application/pdf',
                    'size' => 8,
                ],
            ],
            'follow_up' => [
                'summary' => 'Sudah ditindaklanjuti.',
                'attachment' => [
                    'name' => 'hasil.pdf',
                    'path' => 'complaints/BHM-2026-0011/tindak-lanjut/hasil.pdf',
                    'mime' => 'application/pdf',
                    'size' => 8,
                ],
            ],
        ]);

        $this->get('/internal/complaints/BHM-2026-0011/lampiran/0')->assertRedirect('/login');

        $this->actingAs($verifier)
            ->get('/internal/complaints/BHM-2026-0011/lampiran/0')
            ->assertDownload('bukti.pdf');

        $this->actingAs($verifier)
            ->get('/internal/complaints/BHM-2026-0011/tindak-lanjut')
            ->assertDownload('hasil.pdf');

        $this->actingAs($verifier)
            ->get('/internal/complaints/BHM-2026-0011/lampiran/1')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get('/internal/complaints/BHM-2026-0011/lampiran/0')
            ->assertDownload('bukti.pdf');

        $this->actingAs($admin)
            ->get('/internal/complaints/BHM-2026-0011/tindak-lanjut')
            ->assertDownload('hasil.pdf');

        $this->actingAs($other)
            ->get('/internal/complaints/BHM-2026-0011/lampiran/0')
            ->assertForbidden();

        $this->actingAs($other)
            ->get('/internal/complaints/BHM-2026-0011/tindak-lanjut')
            ->assertForbidden();
    }
}
