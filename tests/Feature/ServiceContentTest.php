<?php

namespace Tests\Feature;

use App\Models\ServiceContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin', 'is_active' => true])->save();

        return $user;
    }

    private function input(array $override = []): array
    {
        return array_replace([
            'title' => 'Contoh dummy layanan uji', 'category' => 'SOP', 'service_category' => 'Kategori uji',
            'agency' => 'Instansi uji', 'type' => 'daerah', 'body' => 'Deskripsi dummy untuk pengujian.',
            'requirements' => 'Persyaratan dummy', 'procedure' => 'Tahapan dummy', 'status' => 'Draf',
        ], $override);
    }

    private function record(array $override = [], ?string $path = null): ServiceContent
    {
        $key = (string) Str::uuid();

        return ServiceContent::create(['collection' => 'contents', 'record_key' => $key, 'payload' => $this->input($override), 'file_path' => $path]);
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('panduan.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    }

    public function test_crud_upload_publication_and_download_use_the_same_record(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->get('/admin/konten')->assertOk();
        $this->get('/admin/konten/tambah')->assertOk();
        $this->post('/admin/konten', $this->input(['file' => $this->pdf()]))->assertRedirect('/admin/konten');
        $record = ServiceContent::firstOrFail();
        $path = $record->file_path;
        Storage::disk('local')->assertExists($path);
        $this->get('/admin/konten/'.$record->record_key.'/edit')->assertOk()->assertSee('Ganti dokumen');
        $this->get('/layanan')->assertDontSee($record->payload['title']);
        $this->get('/layanan/'.$record->record_key)->assertNotFound();
        $this->get('/layanan/'.$record->record_key.'/dokumen')->assertDownload('contoh-dummy-layanan-uji.pdf');
        $this->put('/admin/konten/'.$record->record_key, $this->input(['status' => 'Terbit', 'title' => 'Layanan uji diperbarui', 'file' => $this->pdf()]))->assertRedirect('/admin/konten');
        $record->refresh();
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists($record->file_path);
        $this->assertDatabaseCount('dashboard_records', 1);
        auth()->forgetGuards();
        $this->get('/layanan')->assertOk()->assertSee('Layanan uji diperbarui');
        $this->get('/layanan/'.$record->record_key)->assertOk()->assertSee('Tahapan dummy')->assertSee('Informasi belum tersedia');
        $response = $this->get('/layanan/'.$record->record_key.'/dokumen')->assertDownload('layanan-uji-diperbarui.pdf');
        $this->assertSame(Storage::disk('local')->get($record->file_path), $response->streamedContent());
        $this->actingAs(User::where('role', 'admin')->first());
        $this->delete('/admin/konten/'.$record->record_key)->assertRedirect('/admin/konten');
        Storage::disk('local')->assertMissing($record->file_path);
        $this->assertDatabaseCount('dashboard_records', 0);
        $this->assertDatabaseCount('dashboard_activities', 3);
    }

    public function test_anonymous_inactive_and_verifier_cannot_manage_or_read_draft_documents(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('services/draft.pdf', '%PDF-1.4');
        $record = $this->record([], 'services/draft.pdf');
        $this->get('/admin/konten')->assertRedirect('/login');
        $this->get('/layanan/'.$record->record_key.'/dokumen')->assertNotFound();
        foreach ([['verifikator', true], ['admin', false]] as [$role, $active]) {
            $user = User::factory()->create();
            $user->forceFill(['role' => $role, 'is_active' => $active])->save();
            $this->actingAs($user);
            $this->get('/admin/konten')->assertForbidden();
            $this->get('/admin/konten/tambah')->assertForbidden();
            $this->get('/admin/konten/'.$record->record_key.'/edit')->assertForbidden();
            $this->post('/admin/konten', $this->input())->assertForbidden();
            $this->put('/admin/konten/'.$record->record_key, $this->input())->assertForbidden();
            $this->delete('/admin/konten/'.$record->record_key)->assertForbidden();
            $this->get('/layanan/'.$record->record_key.'/dokumen')->assertNotFound();
        }
        $this->assertDatabaseCount('dashboard_records', 1);
    }

    public function test_validation_rejects_missing_fields_invalid_pdf_size_and_extension(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $this->postJson('/admin/konten', [])->assertUnprocessable()->assertJsonValidationErrors(['title', 'body', 'agency', 'service_category', 'type', 'status']);
        foreach ([UploadedFile::fake()->createWithContent('fake.pdf', 'plain text')->mimeType('text/plain'), UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'), UploadedFile::fake()->createWithContent('bad.txt', "%PDF-1.4\n%%EOF")] as $file) {
            $this->postJson('/admin/konten', $this->input(['file' => $file]))->assertUnprocessable()->assertJsonValidationErrors('file');
        }
        $this->assertDatabaseCount('dashboard_records', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_search_filters_pagination_reset_and_escaping(): void
    {
        $record = $this->record(['status' => 'Terbit', 'title' => '<script>alert(1)</script>', 'body' => 'Kata unik pencarian']);
        $this->record(['status' => 'Terbit', 'agency' => 'Instansi lain', 'service_category' => 'Kategori lain', 'type' => 'umum', 'title' => 'Hasil lain']);
        $this->record(['agency' => 'Instansi rahasia', 'title' => 'Draf tersembunyi']);
        foreach (['q=unik', 'q=Instansi+uji', 'q=Kategori+uji', 'category=Kategori+uji', 'agency=Instansi+uji', 'type=daerah', 'q=unik&category=Kategori+uji&agency=Instansi+uji&type=daerah'] as $query) {
            $this->get('/layanan?'.$query)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('Hasil lain')->assertDontSee('Draf tersembunyi')->assertDontSee('Instansi rahasia');
        }
        $this->get('/layanan?q=tidak-ada')->assertOk()->assertSee('Tidak ada informasi yang cocok');
        $this->get('/layanan?q=%27%20OR%201=1--')->assertOk()->assertSee('Tidak ada informasi yang cocok');
        $this->get('/layanan?q=')->assertOk()->assertSee('Hasil lain');
        $this->get('/layanan')->assertOk()->assertSee('Hasil lain')->assertDontSee('<script>alert(1)</script>', false);
        for ($i = 0; $i < 13; $i++) {
            $this->record(['status' => 'Terbit', 'title' => 'Pagination '.$i]);
        }
        $this->get('/layanan?agency=Instansi+uji')->assertOk()->assertViewHas('records', fn ($records) => $records->count() === 12 && $records->total() === 14)->assertSee('agency=Instansi%20uji', false);
        $this->get('/layanan?agency=Instansi+uji&page=2')->assertOk()->assertViewHas('records', fn ($records) => $records->count() === 2);
        $this->get('/layanan/'.$record->record_key)->assertOk();
    }

    public function test_missing_document_and_other_collections_are_not_exposed(): void
    {
        Storage::fake('local');
        $record = $this->record(['status' => 'Terbit'], 'services/missing.pdf');
        $this->get('/layanan/'.$record->record_key)->assertOk()->assertSee('Dokumen belum tersedia')->assertDontSee('/dokumen');
        $this->get('/layanan/'.$record->record_key.'/dokumen')->assertNotFound();
        DB::table('dashboard_records')->insert(['collection' => 'documents', 'record_key' => 'JDIH', 'payload' => json_encode(['status' => 'Terbit', 'title' => 'Dokumen lain']), 'file_path' => 'private.pdf']);
        $this->get('/layanan/JDIH')->assertNotFound();
        $this->get('/layanan/JDIH/dokumen')->assertNotFound();
        $this->get('/layanan')->assertDontSee('Dokumen lain');
    }

    public function test_update_without_upload_keeps_document_and_failed_transaction_removes_new_upload(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $record = $this->record([], 'services/original.pdf');
        Storage::disk('local')->put($record->file_path, '%PDF-1.4');
        $this->put('/admin/konten/'.$record->record_key, $this->input(['title' => 'Berubah']))->assertRedirect('/admin/konten');
        $this->assertSame('services/original.pdf', $record->fresh()->file_path);
        $this->put('/admin/konten/tidak-ada', $this->input(['file' => $this->pdf()]))->assertNotFound();
        $this->assertSame(['services/original.pdf'], Storage::disk('local')->allFiles());
    }

    public function test_failed_save_rolls_back_data_and_cleans_upload_then_unpublishing_hides_content(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $record = $this->record(['status' => 'Terbit'], 'services/original.pdf');
        Storage::disk('local')->put($record->file_path, '%PDF-1.4');
        ServiceContent::saving(function (ServiceContent $content) {
            if ($content->payload['title'] === 'Simulasi gagal simpan') {
                throw new \RuntimeException('Simulasi kegagalan penyimpanan');
            }
        });
        $this->from('/admin/konten/'.$record->record_key.'/edit')->put('/admin/konten/'.$record->record_key, $this->input(['title' => 'Simulasi gagal simpan', 'file' => $this->pdf()]))
            ->assertRedirect('/admin/konten/'.$record->record_key.'/edit')->assertSessionHasErrors('save');
        $this->assertSame('Terbit', $record->fresh()->payload['status']);
        $this->assertSame(['services/original.pdf'], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('dashboard_activities', 0);
        $this->put('/admin/konten/'.$record->record_key, $this->input())->assertRedirect('/admin/konten');
        $this->get('/layanan')->assertDontSee($record->payload['title']);
        $this->get('/layanan/'.$record->record_key)->assertNotFound();
        auth()->forgetGuards();
        $this->get('/layanan/'.$record->record_key.'/dokumen')->assertNotFound();
    }
}
