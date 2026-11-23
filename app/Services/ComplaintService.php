<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ComplaintService
{
    // Ambil hasil pre-screening terakhir dari sesi konsultasi chatbot.

    public function prescreeningFor(?string $uuid, ?int $messageId = null): ?array
    {
        if (! $uuid || ! preg_match('/^[A-Za-z0-9_-]{12,64}$/', $uuid)) {
            return null;
        }

        $session = DB::table('consultation_sessions')->where('session_uuid', $uuid)->first();
        if (! $session) {
            return null;
        }

        $base = DB::table('consultation_messages')->where('consultation_session_id', $session->id);
        $assistant = $messageId
            ? (clone $base)->where('id', $messageId)->where('sender', 'assistant')->first()
            : null;
        if (! $assistant) {
            $assistant = (clone $base)->where('sender', 'assistant')->latest('id')->first();
        }
        $user = (clone $base)->where('sender', 'user')->latest('id')->first();

        if (! $assistant) {
            return null;
        }

        $citations = json_decode($assistant->citations ?? '[]', true) ?: [];
        $legalAnalysis = isset($assistant->legal_analysis) && $assistant->legal_analysis
            ? (json_decode((string) $assistant->legal_analysis, true) ?: null)
            : null;

        return [
            'session_id' => (int) $session->id,
            'session_uuid' => $uuid,
            'message_id' => (int) $assistant->id,
            'chronology' => $user?->content,
            'answer' => $assistant->content,
            'citations' => $citations,
            'legal_analysis' => $legalAnalysis,
        ];
    }

    // Nomor tiket berurutan: BHM-tahun-nomor (contoh BHM-2026-0001).

    public function newTicket(): string
    {
        $year = now('Asia/Jakarta')->year;
        $prefix = "BHM-{$year}-";

        $last = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', 'like', $prefix.'%')
            ->pluck('record_key')
            ->map(fn ($key) => (int) Str::after($key, $prefix))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    // Simpan pengaduan warga ke dashboard_records (collection 'complaints').

    public function create(array $input, array $files = []): string
    {
        $anon = (bool) ($input['is_anonim'] ?? false);
        $prefill = $this->prescreeningFor($input['session_uuid'] ?? null);

        return DB::transaction(function () use ($input, $files, $anon, $prefill) {
            // Samakan dengan kunci yang dipakai dashboard supaya dua pengaduan tidak mendapat nomor sama
            DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
            $ticket = $this->newTicket();
            $now = now();

            $attachments = [];
            foreach ($files as $file) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store('complaints/'.$ticket, 'local'),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }

            $references = collect($prefill['citations'] ?? [])
                ->map(fn ($c) => trim(data_get($c, 'document_name', 'Rujukan').' ('.data_get($c, 'document_number', '-').') '.data_get($c, 'article_label', '')))
                ->filter()->values()->all();

            $aiSummary = $prefill['answer'] ?? null;
            if (! empty($prefill['legal_analysis']['pasal_relevan']) && is_array($prefill['legal_analysis']['pasal_relevan'])) {
                $lines = [];
                foreach ($prefill['legal_analysis']['pasal_relevan'] as $p) {
                    $lines[] = '- '.($p['pasal'] ?? '').' ('.($p['dokumen'] ?? '').'): '.($p['penjelasan_awam'] ?? '');
                }
                foreach ($prefill['legal_analysis']['ancaman_sanksi'] ?? [] as $s) {
                    $lines[] = '- Estimasi Sanksi '.($s['pasal'] ?? '').' ['.strtoupper((string) ($s['jenis'] ?? 'lainnya')).']: '.($s['bunyi_sanksi_ringkas'] ?? '');
                }
                if (! empty($lines)) {
                    $aiSummary = implode("\n", $lines);
                }
            }

            $payload = [
                'id' => $ticket,
                'date' => $now->toDateString(),
                'category' => $input['kategori'],
                'reporter' => $anon ? 'Anonim' : $input['nama_pelapor'],
                'contact' => $anon ? null : ($input['kontak'] ?? null),
                'summary' => Str::limit($input['uraian'], 140),
                'status' => 'Baru',
                'priority' => 'Sedang',
                'officer' => null,
                'reported' => $input['instansi_terlapor'],
                'location' => $input['lokasi'],
                'incidentDate' => $input['tanggal_kejadian'],
                'chronology' => $input['uraian'],
                'agency' => null,
                'ai' => $aiSummary,
                'references' => $references,
                'attachments' => $attachments,
                'notes' => [],
                'history' => [['date' => $now->toIso8601String(), 'text' => 'received · Baru']],
                'consultation_session_uuid' => $prefill['session_uuid'] ?? null,
                'consultation_message_id' => $prefill['message_id'] ?? null,
            ];

            $insertData = [
                'collection' => 'complaints',
                'record_key' => $ticket,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'assigned_to' => null,
                'file_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('dashboard_records', 'consultation_session_id')) {
                $insertData['consultation_session_id'] = $prefill['session_id'] ?? null;
                $insertData['consultation_message_id'] = $prefill['message_id'] ?? null;
            }

            DB::table('dashboard_records')->insert($insertData);

            return $ticket;
        });
    }

    public function submitCompleteness(string $ticket, string $note, array $files = []): void
    {
        DB::transaction(function () use ($ticket, $note, $files) {
            DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);

            $record = DB::table('dashboard_records')
                ->where('collection', 'complaints')
                ->where('record_key', $ticket)
                ->lockForUpdate()
                ->first();

            abort_unless($record, 404);

            $payload = json_decode($record->payload, true) ?: [];

            if (($payload['status'] ?? null) !== 'Menunggu Kelengkapan') {
                throw ValidationException::withMessages([
                    'lampiran' => 'Pengaduan ini tidak sedang menunggu kelengkapan dokumen.',
                ]);
            }

            $existingAttachments = is_array($payload['attachments'] ?? null) ? $payload['attachments'] : [];
            if (count($existingAttachments) + count($files) > 10) {
                throw ValidationException::withMessages([
                    'lampiran' => 'Jumlah total lampiran per tiket maksimal 10 berkas.',
                ]);
            }

            $now = now();
            $uploaded = [];
            foreach ($files as $file) {
                $uploaded[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store('complaints/'.$ticket, 'local'),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }

            $payload['attachments'] = array_values([...$existingAttachments, ...$uploaded]);

            $submissions = is_array($payload['completeness_submissions'] ?? null) ? $payload['completeness_submissions'] : [];
            $submissions[] = [
                'date' => $now->toIso8601String(),
                'note' => $note,
                'attachments' => $uploaded,
            ];
            $payload['completeness_submissions'] = $submissions;

            $completenessRequest = is_array($payload['completeness_request'] ?? null) ? $payload['completeness_request'] : [];
            $completenessRequest['fulfilled_at'] = $now->toIso8601String();
            $payload['completeness_request'] = $completenessRequest;

            $payload['status'] = 'Menunggu Verifikasi';
            $payload['history'] = is_array($payload['history'] ?? null) ? $payload['history'] : [];
            $payload['history'][] = [
                'date' => $now->toIso8601String(),
                'text' => 'kelengkapan diterima · Menunggu Verifikasi',
            ];

            DB::table('dashboard_records')
                ->where('collection', 'complaints')
                ->where('record_key', $ticket)
                ->update([
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                ]);
        });
    }

    // Cari pengaduan berdasarkan nomor tiket

    public function trackByTicket(string $ticket): ?array
    {
        $record = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', $ticket)
            ->first();

        if (! $record) {
            return null;
        }

        $p = json_decode($record->payload, true) ?: [];
        $map = [
            'Baru' => 'Diterima',
            'Menunggu Verifikasi' => 'Diperiksa',
            'Menunggu Disposisi' => 'Diperiksa',
            'Menunggu Kelengkapan' => 'Perlu Dilengkapi',
            'Dalam Proses' => 'Diproses',
            'Selesai' => 'Selesai',
            'Ditolak' => 'Ditolak',
        ];
        $label = fn ($s) => $map[$s] ?? $s;

        $fmt = function (?string $iso) {
            if (! $iso) {
                return null;
            }

            return \Carbon\Carbon::parse($iso)->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i').' WIB';
        };

        $history = collect($p['history'] ?? [])->map(fn ($h) => [
            'date' => $fmt($h['date'] ?? null),
            'status' => $label(trim(Str::afterLast($h['text'] ?? '', '·'))),
        ])->values()->all();

        return [
            'ticket' => $ticket,
            'status' => $label($p['status'] ?? 'Baru'),
            'category' => $p['category'] ?? null,
            'date' => isset($p['date']) ? \Carbon\Carbon::parse($p['date'])->locale('id')->translatedFormat('d M Y') : null,
            'agency' => $p['agency'] ?? null,
            'history' => $history,
            'rejection_reason' => $p['rejection_reason'] ?? null,
            'requested_documents' => array_values(array_filter(
                is_array($p['completeness_request']['documents'] ?? null) ? $p['completeness_request']['documents'] : [],
                fn ($item) => is_string($item) && trim($item) !== ''
            )),
            'request_note' => $p['completeness_request']['note'] ?? null,
            'dispositions' => collect($p['dispositions'] ?? [])->map(fn ($d) => [
                'agency' => $d['agency'] ?? null,
                'date' => $fmt($d['date'] ?? null),
            ])->filter(fn ($d) => ! empty($d['agency']))->values()->all(),
            'follow_up' => isset($p['follow_up']) && is_array($p['follow_up']) && ! empty($p['follow_up']['summary']) ? [
                'summary' => $p['follow_up']['summary'],
                'updated_at' => $fmt($p['follow_up']['updated_at'] ?? null),
            ] : null,
        ];
    }
}