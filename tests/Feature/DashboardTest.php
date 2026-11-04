<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin', bool $active = true): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->is_active = $active;
        $user->save();

        return $user;
    }

    private function complaint(string $key, int $assigned): void
    {
        DB::table('dashboard_records')->insert([
            'collection' => 'complaints', 'record_key' => $key, 'assigned_to' => $assigned,
            'payload' => json_encode(['id' => $key, 'status' => 'Baru', 'notes' => [], 'history' => []]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_guests_and_inactive_users_cannot_access_internal_data(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->getJson('/internal/data')->assertUnauthorized();
        $this->actingAs($this->user('admin', false))->getJson('/internal/data')->assertForbidden();
    }

    public function test_verifier_cannot_access_admin_pages_or_management_endpoints(): void
    {
        $this->actingAs($this->user('verifikator'));
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/verifikator/akun')->assertForbidden();
        $this->postJson('/internal/data/contents', [])->assertForbidden();
        $this->deleteJson('/internal/data/documents/D1')->assertForbidden();
        $this->get('/internal/files/D1')->assertForbidden();
    }

    public function test_verifier_only_reads_and_changes_assigned_complaints(): void
    {
        $user = $this->user('verifikator');
        $other = $this->user('verifikator');
        $this->complaint('OWN', $user->id);
        $this->complaint('OTHER', $other->id);
        $this->actingAs($user)->getJson('/internal/data')->assertOk()->assertJsonCount(1, 'complaints')->assertJsonPath('complaints.0.id', 'OWN')->assertJsonCount(0, 'accounts');
        $this->postJson('/internal/data/complaints', ['id' => 'OTHER', 'action' => 'verify', 'note' => 'Sudah lengkap'])->assertForbidden();
        $this->postJson('/internal/data/complaints', ['id' => 'OWN', 'action' => 'verify', 'note' => 'Sudah lengkap'])->assertOk();
        $this->assertDatabaseCount('dashboard_activities', 1);
    }

    public function test_disposition_requires_verification_and_destination(): void
    {
        $user = $this->user('verifikator');
        $this->complaint('TEST', $user->id);
        $this->actingAs($user);
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'dispatch', 'note' => 'Disposisi laporan', 'agency' => 'Instansi tujuan'])->assertUnprocessable();
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'verify', 'note' => 'Dokumen lengkap'])->assertOk();
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'dispatch', 'note' => 'Disposisi laporan'])->assertUnprocessable();
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'dispatch', 'note' => 'Disposisi laporan', 'agency' => 'Instansi tujuan'])->assertOk();
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'status', 'note' => 'Hasil tindak lanjut diterima', 'status' => 'Selesai'])->assertOk();
        $this->postJson('/internal/data/complaints', ['id' => 'TEST', 'action' => 'verify', 'note' => 'Verifikasi ulang'])->assertUnprocessable();
    }

    public function test_content_crud_persists_and_audits_changes(): void
    {
        $this->actingAs($this->user());
        $input = ['id' => 'C1', 'title' => 'SOP layanan', 'category' => 'SOP', 'body' => 'Isi layanan', 'status' => 'Draf'];
        $this->postJson('/internal/data/contents', $input)->assertOk();
        $this->postJson('/internal/data/contents', [...$input, 'status' => 'Terbit'])->assertOk();
        $this->getJson('/internal/data')->assertJsonPath('contents.0.status', 'Terbit');
        $this->deleteJson('/internal/data/contents/C1')->assertOk();
        $this->assertDatabaseCount('dashboard_records', 0);
        $this->assertDatabaseCount('dashboard_activities', 3);
    }

    public function test_invalid_budget_and_self_revocation_are_rejected(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->postJson('/internal/data/budgets', ['id' => 'B1', 'title' => 'Layanan', 'year' => 2026, 'allocation' => 10, 'realized' => 11])->assertUnprocessable();
        $this->postJson('/internal/data/accounts', ['id' => (string) $admin->id, 'name' => $admin->name, 'email' => $admin->email, 'role' => 'verifikator', 'status' => 'Aktif'])->assertUnprocessable();
    }

    public function test_pdf_upload_is_private_and_capacity_is_enforced(): void
    {
        Storage::fake('local');
        $this->actingAs($this->user());
        $metadata = ['id' => 'D1', 'title' => 'Peraturan', 'number' => '1', 'year' => 2026, 'category' => 'Umum', 'summary' => 'Ringkasan dokumen'];
        $this->postJson('/internal/data/documents', $metadata)->assertUnprocessable();
        $this->post('/internal/data/documents', [...$metadata, 'file' => UploadedFile::fake()->createWithContent('aturan.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF")], ['Accept' => 'application/json'])->assertOk();
        $this->getJson('/internal/data')->assertJsonPath('documents.0.status', 'Menunggu');
        $this->get('/internal/files/D1')->assertDownload('aturan.pdf');
        for ($i = 2; $i <= 100; $i++) {
            DB::table('dashboard_records')->insert(['collection' => 'documents', 'record_key' => 'D'.$i, 'payload' => '{}', 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->post('/internal/data/documents', [...$metadata, 'id' => 'D101', 'file' => UploadedFile::fake()->createWithContent('aturan.pdf', "%PDF-1.4\n%%EOF")], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertDatabaseCount('dashboard_records', 100);
    }

    public function test_login_logout_and_disabled_accounts(): void
    {
        $user = $this->user();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $user->is_active = false;
        $user->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_demo_is_local_only_and_verifier_routes_are_restricted(): void
    {
        $this->get('/demo/admin/dashboard')->assertOk();
        $this->get('/demo/verifikator/akun')->assertForbidden();
        $this->app->instance('env', 'production');
        $this->get('/demo/admin/dashboard')->assertNotFound();
    }
}
