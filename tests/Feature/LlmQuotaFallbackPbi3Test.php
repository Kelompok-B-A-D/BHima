<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LegalAnalysisService;
use App\Services\LegalRetrievalService;
use App\Services\LlmQuotaService;
use Carbon\Carbon;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmQuotaFallbackPbi3Test extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function seedKnowledgeBase(): void
    {
        $this->seed(LegalKnowledgeSeeder::class);
    }

    private function configureExternalApi(array $overrides = []): void
    {
        config(array_merge([
            'legal_analysis.provider' => 'deepseek',
            'legal_analysis.deepseek.api_key' => 'sk-test-pbi3-secret-998877',
            'legal_analysis.deepseek.base_url' => 'https://ai.paas.id',
            'legal_analysis.deepseek.model' => 'deepseek-v4.1-flash',
            'legal_analysis.deepseek.max_retries' => 0,
            'rag.llm.provider' => 'api',
            'rag.llm.api_key' => 'sk-test-pbi3-secret-998877',
            'rag.llm.base_url' => 'https://ai.paas.id',
            'rag.llm.model' => 'deepseek-v4.1-flash',
            'llm_quota.daily_token_limit' => 200000,
            'llm_quota.daily_request_limit' => 500,
            'llm_quota.per_request_token_cap' => 3000,
            'llm_quota.circuit_failure_threshold' => 3,
            'llm_quota.circuit_cooldown_minutes' => 5,
        ], $overrides));
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    private function buildValidAnalysisJson(array $chunks): string
    {
        return json_encode([
            'rujukan' => [
                [
                    'dokumen' => $chunks[0]['document_name'],
                    'nomor_tahun' => $chunks[0]['document_number'].' Tahun '.$chunks[0]['document_year'],
                    'tautan' => $chunks[0]['source_url'],
                ],
            ],
            'pasal_relevan' => [
                [
                    'pasal' => $chunks[0]['article_label'],
                    'dokumen' => $chunks[0]['document_name'],
                    'penjelasan_awam' => 'Pasal yang berpotensi terkait dengan pengurusan layanan kependudukan gratis.',
                    'alasan_terkait_kronologi' => 'Kronologi warga berkaitan dengan dugaan pungutan saat pengurusan KTP-el.',
                ],
            ],
            'ancaman_sanksi' => [
                [
                    'pasal' => $chunks[0]['article_label'],
                    'jenis' => 'lainnya',
                    'bunyi_sanksi_ringkas' => 'Tidak tercantum dalam potongan pasal yang ditemukan',
                    'dokumen' => $chunks[0]['document_name'],
                ],
            ],
            'perlu_eskalasi' => true,
            'catatan' => 'Analisis AI berhasil berdasarkan dokumen JDIH.',
        ], JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    /**
     * Pengujian 1:
     * Kuota habis (batas token maupun batas permintaan): Http::assertNothingSent(),
     * respons mode=fallback, fallback_reason=quota_exceeded, pasal JDIH tetap muncul.
     */
    public function test_quota_exceeded_blocks_network_and_returns_jdih_catalog_fallback(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'llm_quota.daily_token_limit' => 5000,
            'llm_quota.per_request_token_cap' => 3000,
        ]);

        // Isi pemakaian hari ini mendekati batas (3000 + estimasi 3000 > 5000)
        app(LlmQuotaService::class)->recordUsage('analysis', 'deepseek', 1500, 1500, 3000);

        Http::fake();

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik yang hilang di Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai.',
        ]);

        Http::assertNothingSent();

        $response->assertOk()
            ->assertJsonPath('meta.mode', 'fallback')
            ->assertJsonPath('meta.fallback_reason', 'quota_exceeded')
            ->assertJsonPath('meta.provider', 'catalog_fallback')
            ->assertJsonPath('assistant_message.retrieval_meta.mode', 'fallback')
            ->assertJsonPath('assistant_message.retrieval_meta.fallback_reason', 'quota_exceeded')
            ->assertJsonPath('assistant_message.legal_analysis.meta.mode', 'fallback')
            ->assertJsonPath('assistant_message.legal_analysis.meta.fallback_reason', 'quota_exceeded')
            ->assertJsonPath('assistant_message.legal_analysis.meta.provider', 'catalog_fallback')
            ->assertJsonPath('assistant_message.legal_analysis.perlu_eskalasi', true);

        $analysis = $response->json('assistant_message.legal_analysis');
        $this->assertNotEmpty($analysis['rujukan']);
        $this->assertNotEmpty($analysis['pasal_relevan']);
        $this->assertNotEmpty($analysis['ancaman_sanksi']);
        $this->assertNotEmpty($response->json('assistant_message.citations'));

        // Pastikan log tercatat quota_blocked untuk consultation dan analysis tanpa menambah tokens_used
        $this->assertSame(
            2,
            DB::table('llm_usage_logs')->where('status', 'quota_blocked')->where('error_code', 'quota_exceeded')->count()
        );
        $this->assertSame(3000, (int) DB::table('llm_daily_usage')->value('tokens_used'));

        // Uji juga batas jumlah permintaan (LLM_DAILY_REQUEST_LIMIT)
        config([
            'llm_quota.daily_token_limit' => 500000,
            'llm_quota.daily_request_limit' => 1,
        ]);
        $analysisResponse = $this->postJson('/api/konsultasi/analisis', [
            'chronology' => 'Saya mengurus KTP elektronik di Disdukcapil tetapi dimintai uang rokok Rp100.000.',
        ]);

        Http::assertNothingSent();
        $analysisResponse->assertOk()
            ->assertJsonPath('meta.mode', 'fallback')
            ->assertJsonPath('meta.fallback_reason', 'quota_exceeded')
            ->assertJsonPath('meta.provider', 'catalog_fallback');
        $this->assertNotEmpty($analysisResponse->json('pasal_relevan'));
    }

    /**
     * Pengujian 2:
     * API mengembalikan 429, 500, dan timeout: fallback katalog JDIH muncul, aplikasi tidak error,
     * dan pesan error teknis tidak ditampilkan mentah ke warga.
     */
    public function test_api_429_500_and_timeout_produce_catalog_fallback_without_raw_technical_errors(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'rag.llm.provider' => 'local_grounded',
            'llm_quota.circuit_failure_threshold' => 10,
        ]);

        $chunks = app(LegalRetrievalService::class)->retrieve(
            'Saya mengurus KTP elektronik yang hilang di Disdukcapil tetapi dimintai uang rokok Rp100.000.',
            3
        )['chunks'];

        $analysisService = app(LegalAnalysisService::class);

        Http::fakeSequence('https://ai.paas.id/chat/completions')
            ->push(['message' => 'Too Many Requests'], 429)
            ->push(['error' => 'upstream crash 500'], 500)
            ->pushFailedConnection('cURL error 28: Operation timed out after 20000 milliseconds');

        // 1. HTTP 429 Rate Limit
        $res429 = $analysisService->analyze('Pengurusan KTP-el dimintai uang rokok Rp100.000.', $chunks);
        $this->assertSame('fallback', $res429['meta']['mode']);
        $this->assertSame('rate_limit', $res429['meta']['fallback_reason']);
        $this->assertSame('catalog_fallback', $res429['meta']['provider']);
        $this->assertNotEmpty($res429['pasal_relevan']);
        $this->assertStringNotContainsString('HTTP 429', $res429['catatan']);

        // 2. HTTP 500 Internal Server Error
        $res500 = $analysisService->analyze('Pengurusan KTP-el dimintai uang rokok Rp100.000.', $chunks);
        $this->assertSame('fallback', $res500['meta']['mode']);
        $this->assertSame('http_error', $res500['meta']['fallback_reason']);
        $this->assertSame('catalog_fallback', $res500['meta']['provider']);
        $this->assertNotEmpty($res500['pasal_relevan']);
        $this->assertStringNotContainsString('upstream crash', $res500['catatan']);
        $this->assertStringNotContainsString('HTTP 500', $res500['catatan']);

        // 3. Timeout / ConnectionException
        $resTimeout = $analysisService->analyze('Pengurusan KTP-el dimintai uang rokok Rp100.000.', $chunks);
        $this->assertSame('fallback', $resTimeout['meta']['mode']);
        $this->assertSame('timeout', $resTimeout['meta']['fallback_reason']);
        $this->assertSame('catalog_fallback', $resTimeout['meta']['provider']);
        $this->assertNotEmpty($resTimeout['pasal_relevan']);
        $this->assertStringNotContainsString('cURL error 28', $resTimeout['catatan']);
    }

    /**
     * Pengujian 3:
     * Gagal beruntun mencapai ambang (3): circuit terbuka, panggilan berikutnya tidak menyentuh jaringan
     * (fallback_reason=circuit_open), dan tertutup kembali setelah cooldown selesai.
     */
    public function test_circuit_breaker_opens_after_consecutive_failures_and_closes_after_cooldown(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 24, 10, 0, 0, 'Asia/Jakarta'));

        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'rag.llm.provider' => 'local_grounded',
            'llm_quota.circuit_failure_threshold' => 3,
            'llm_quota.circuit_cooldown_minutes' => 5,
        ]);

        $chunks = app(LegalRetrievalService::class)->retrieve(
            'Pengurusan KTP elektronik dimintai uang rokok oleh petugas.',
            3
        )['chunks'];
        $validJson = $this->buildValidAnalysisJson($chunks);
        $analysisService = app(LegalAnalysisService::class);

        Http::fakeSequence('https://ai.paas.id/chat/completions')
            ->push(['error' => 'Service unavailable 1'], 503)
            ->push(['error' => 'Service unavailable 2'], 503)
            ->push(['error' => 'Service unavailable 3'], 503)
            ->push([
                'choices' => [['message' => ['content' => $validJson]]],
                'usage' => ['prompt_tokens' => 410, 'completion_tokens' => 190, 'total_tokens' => 600],
            ], 200);

        // 3 kegagalan beruntun -> membuka circuit breaker
        for ($i = 1; $i <= 3; $i++) {
            $failed = $analysisService->analyze('Pengurusan KTP-el dimintai pungutan liar.', $chunks);
            $this->assertSame('fallback', $failed['meta']['mode']);
            $this->assertSame('http_error', $failed['meta']['fallback_reason']);
        }
        $this->assertCount(3, Http::recorded());

        // Panggilan ke-4 dalam masa cooldown TIDAK menyentuh jaringan
        $blocked = $analysisService->analyze('Pengurusan KTP-el dimintai pungutan liar.', $chunks);
        $this->assertSame('fallback', $blocked['meta']['mode']);
        $this->assertSame('circuit_open', $blocked['meta']['fallback_reason']);
        $this->assertNotEmpty($blocked['pasal_relevan']);
        $this->assertCount(3, Http::recorded());

        // Setelah cooldown 5 menit terlewati, circuit menutup dan panggilan ke-5 menembus API lalu mereset failure count
        Carbon::setTestNow(Carbon::create(2026, 11, 24, 10, 6, 0, 'Asia/Jakarta'));

        $recovered = $analysisService->analyze('Pengurusan KTP-el dimintai pungutan liar.', $chunks);
        $this->assertSame('ai', $recovered['meta']['mode']);
        $this->assertSame('ok', $recovered['meta']['status']);
        $this->assertCount(4, Http::recorded());

        $status = app(LlmQuotaService::class)->status();
        $this->assertFalse($status['circuit_open']);
        $this->assertSame(0, $status['consecutive_failures']);
        $this->assertSame(600, $status['tokens_used']);
    }

    /**
     * Pengujian 4:
     * Penghitung bersama mencakup konsultasi + analisis sesuai usage.total_tokens,
     * dan pergantian hari (Asia/Jakarta via Carbon::setTestNow) mereset hitungan.
     */
    public function test_shared_counter_tracks_consultation_and_analysis_tokens_and_resets_next_day(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 24, 23, 55, 0, 'Asia/Jakarta'));

        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'llm_quota.daily_token_limit' => 200000,
            'llm_quota.per_request_token_cap' => 3000,
        ]);

        $chunks = app(LegalRetrievalService::class)->retrieve(
            'Saya mengurus KTP elektronik yang hilang di Disdukcapil tetapi oknum petugas meminta uang rokok Rp100.000.',
            5
        )['chunks'];
        $validJson = $this->buildValidAnalysisJson($chunks);

        Http::fakeSequence('https://ai.paas.id/chat/completions')
            // 1. Panggilan LegalAssistantService (consultation)
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => "(a) Ringkasan Masalah\nDugaan pungli pengurusan KTP-el.\n\n(b) Pasal/Aturan yang Relevan dan Artinya\nBerdasarkan Pasal 79A pengurusan dokumen kependudukan gratis.\n\n(c) Langkah yang Bisa Dilakukan Warga\nLaporkan ke BHIMA.\n\n(d) Rujukan SOP Layanan\nSOP-001.",
                    ],
                ]],
                'usage' => ['prompt_tokens' => 700, 'completion_tokens' => 300, 'total_tokens' => 1000],
            ], 200)
            // 2. Panggilan DeepSeekProvider (analysis)
            ->push([
                'choices' => [['message' => ['content' => $validJson]]],
                'usage' => ['prompt_tokens' => 900, 'completion_tokens' => 450, 'total_tokens' => 1350],
            ], 200);

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik yang hilang di Disdukcapil tetapi oknum petugas meminta uang rokok Rp100.000.',
        ]);

        $response->assertOk()
            ->assertJsonPath('meta.mode', 'ai')
            ->assertJsonPath('assistant_message.retrieval_meta.provider', 'api')
            ->assertJsonPath('assistant_message.legal_analysis.meta.provider', 'deepseek');

        $quotaService = app(LlmQuotaService::class);
        $day1Status = $quotaService->status();
        $this->assertSame('2026-11-24', $day1Status['usage_date']);
        $this->assertSame(2350, $day1Status['tokens_used']);
        $this->assertSame(2, $day1Status['requests_used']);
        $this->assertSame(1000, $day1Status['logs_summary']['by_source']['consultation']['tokens']);
        $this->assertSame(1350, $day1Status['logs_summary']['by_source']['analysis']['tokens']);

        // Pindah ke hari berikutnya 00:05 WIB -> penghitung hari baru otomatis reset ke 0
        Carbon::setTestNow(Carbon::create(2026, 11, 25, 0, 5, 0, 'Asia/Jakarta'));

        $day2Status = $quotaService->status();
        $this->assertSame('2026-11-25', $day2Status['usage_date']);
        $this->assertSame(0, $day2Status['tokens_used']);
        $this->assertSame(0, $day2Status['requests_used']);
        $this->assertTrue($quotaService->checkAllowance()['allowed']);
    }

    /**
     * Pengujian 5:
     * Hasil fallback lolos LegalAnalysisService::isValidSchema(), menjaga aturan "Tidak tercantum...",
     * dan guardrail anti-vonis tetap aktif saat ditanya "apakah saya pasti menang?".
     */
    public function test_fallback_passes_pbi2_schema_sanction_rule_and_verdict_guardrail(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'llm_quota.daily_token_limit' => 100,
            'llm_quota.per_request_token_cap' => 3000,
        ]);

        // Picu kuota habis tanpa panggilan jaringan
        app(LlmQuotaService::class)->recordUsage('analysis', 'deepseek', 50, 50, 100);
        Http::fake();

        $analysisService = app(LegalAnalysisService::class);
        $nonSanctionChunk = DB::table('knowledge_chunks')
            ->where('document_key', 'JDIH-015')
            ->where('article_label', 'Pasal 18 Ayat (1)')
            ->first();
        $this->assertNotNull($nonSanctionChunk);

        $chunkArray = [[
            'chunk_id' => (int) $nonSanctionChunk->id,
            'document_key' => (string) $nonSanctionChunk->document_key,
            'document_name' => (string) $nonSanctionChunk->document_name,
            'document_type' => (string) $nonSanctionChunk->document_type,
            'document_number' => (string) $nonSanctionChunk->document_number,
            'document_year' => (int) $nonSanctionChunk->document_year,
            'article_label' => (string) $nonSanctionChunk->article_label,
            'source_url' => (string) $nonSanctionChunk->source_url,
            'category' => (string) $nonSanctionChunk->category,
            'topic' => (string) $nonSanctionChunk->topic,
            'content' => (string) $nonSanctionChunk->content,
            'score' => 0.84,
        ]];

        $fallbackResult = $analysisService->analyze(
            'Tetangga menggeser patok batas tanah pekarangan saya yang bersertifikat SHM. Apakah saya pasti menang jika melapor?',
            $chunkArray
        );

        Http::assertNothingSent();

        $this->assertTrue(LegalAnalysisService::isValidSchema($fallbackResult));
        $this->assertSame('fallback', $fallbackResult['meta']['mode']);
        $this->assertSame('quota_exceeded', $fallbackResult['meta']['fallback_reason']);
        $this->assertTrue($fallbackResult['meta']['verdict_guardrail_triggered']);
        $this->assertStringContainsString('tidak berwenang memberikan vonis', $fallbackResult['catatan']);
        $this->assertSame(
            'Tidak tercantum dalam potongan pasal yang ditemukan',
            $fallbackResult['ancaman_sanksi'][0]['bunyi_sanksi_ringkas']
        );

        $serialized = json_encode($fallbackResult, JSON_UNESCAPED_UNICODE) ?: '';
        $this->assertStringNotContainsStringIgnoringCase('Anda pasti menang', $serialized);
        $this->assertStringNotContainsStringIgnoringCase('dijamin menang', $serialized);
    }

    /**
     * Pengujian 6:
     * Tombol eskalasi tetap aktif (perlu_eskalasi=true) dan formulir /pengaduan/buat
     * tetap terisi otomatis pada mode fallback.
     */
    public function test_escalation_remains_active_and_prefills_complaint_form_in_fallback_mode(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'llm_quota.daily_token_limit' => 1000,
            'llm_quota.per_request_token_cap' => 3000,
        ]);
        app(LlmQuotaService::class)->recordUsage('analysis', 'deepseek', 500, 500, 1000);
        Http::fake();

        $chatResponse = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik yang hilang di Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat diproses.',
        ]);

        Http::assertNothingSent();
        $chatResponse->assertOk()
            ->assertJsonPath('meta.mode', 'fallback')
            ->assertJsonPath('assistant_message.legal_analysis.perlu_eskalasi', true);

        $sessionUuid = $chatResponse->json('session_id');
        $assistantMessageId = $chatResponse->json('assistant_message.id');

        $formPage = $this->get('/pengaduan/buat?session_id='.$sessionUuid.'&message_id='.$assistantMessageId);
        $formPage->assertOk()
            ->assertSee('Formulir Pengaduan Masyarakat')
            ->assertSee('mengurus KTP elektronik yang hilang')
            ->assertSee('UU No. 24 Tahun 2013');
    }

    /**
     * Pengujian 7:
     * Respons publik dan view tidak pernah memuat API key atau angka kuota rinci,
     * sementara CLI bhima:quota-status dan dashboard internal admin dapat melihat metrik kuota.
     */
    public function test_public_responses_and_views_never_expose_api_key_or_detailed_quota_numbers(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'llm_quota.daily_token_limit' => 123456,
            'llm_quota.daily_request_limit' => 789,
            'llm_quota.per_request_token_cap' => 3000,
        ]);
        app(LlmQuotaService::class)->recordUsage('analysis', 'deepseek', 60000, 62000, 122000);
        Http::fake();

        $chatResponse = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik di Disdukcapil namun diminta biaya tambahan Rp100.000 oleh petugas.',
        ]);
        $chatResponse->assertOk();

        $publicJson = (string) $chatResponse->getContent();
        $this->assertStringNotContainsString('sk-test-pbi3-secret-998877', $publicJson);
        $this->assertStringNotContainsString('tokens_used', $publicJson);
        $this->assertStringNotContainsString('daily_token_limit', $publicJson);
        $this->assertStringNotContainsString('requests_used', $publicJson);
        $this->assertStringNotContainsString('circuit_open_until', $publicJson);
        $this->assertStringNotContainsString('123456', $publicJson);
        $this->assertStringNotContainsString('122000', $publicJson);

        $html = (string) $this->get('/konsultasi')->assertOk()->getContent();
        $this->assertStringNotContainsString('sk-test-pbi3-secret-998877', $html);
        $this->assertStringNotContainsString('123456', $html);
        $this->assertStringContainsString('chat-fallback-banner', $html);
        $this->assertStringContainsString('Layanan analisis AI sedang dibatasi. Berikut rekomendasi pasal dari katalog JDIH.', $html);

        // Perintah CLI bhima:quota-status berjalan sukses
        $this->artisan('bhima:quota-status')->assertExitCode(0);
        $this->artisan('bhima:quota-status', ['--json' => true])->assertExitCode(0);

        // Admin internal memperoleh ringkasan llm_quota di /internal/data, verifikator tidak
        $admin = User::factory()->create(['email' => 'admin-kuota@bhima.test']);
        $admin->role = 'admin';
        $admin->is_active = true;
        $admin->save();

        $verifikator = User::factory()->create(['email' => 'verifikator-kuota@bhima.test']);
        $verifikator->role = 'verifikator';
        $verifikator->is_active = true;
        $verifikator->save();

        $this->actingAs($admin)
            ->getJson('/internal/data')
            ->assertOk()
            ->assertJsonPath('llm_quota.tokens_used', 122000)
            ->assertJsonPath('llm_quota.daily_token_limit', 123456);

        $verifData = $this->actingAs($verifikator)->getJson('/internal/data')->assertOk();
        $this->assertArrayNotHasKey('llm_quota', $verifData->json());
    }

    /**
     * Pengujian 8:
     * LLM_PROVIDER=mock dan RAG_LLM_PROVIDER=local_grounded tetap berjalan normal
     * tanpa tercatat sebagai pemakaian token maupun error pada tabel kuota.
     */
    public function test_mock_provider_runs_without_recording_token_usage(): void
    {
        $this->seedKnowledgeBase();
        config([
            'legal_analysis.provider' => 'mock',
            'rag.llm.provider' => 'local_grounded',
        ]);

        Http::fake();

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik yang hilang di Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000.',
        ]);

        Http::assertNothingSent();
        $response->assertOk()
            ->assertJsonPath('meta.mode', 'ai')
            ->assertJsonPath('assistant_message.legal_analysis.meta.provider', 'mock')
            ->assertJsonPath('assistant_message.legal_analysis.meta.mode', 'ai');

        $this->assertSame(0, DB::table('llm_daily_usage')->count());
        $this->assertSame(0, DB::table('llm_usage_logs')->count());
    }

    /**
     * Edge-case audit:
     * 1. Batas token lebih kecil dari estimasi per panggilan tetap memblokir sejak permintaan pertama hari itu.
     * 2. Respons HTTP 200 ber-JSON tidak valid tetap mencatat token terpakai dan membuka fallback katalog JDIH.
     */
    public function test_fresh_day_cap_limit_and_invalid_json_token_accounting(): void
    {
        $this->seedKnowledgeBase();
        $this->configureExternalApi([
            'rag.llm.provider' => 'local_grounded',
            'llm_quota.daily_token_limit' => 2000,
            'llm_quota.per_request_token_cap' => 3000,
        ]);

        Http::fake([
            'https://ai.paas.id/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'BUKAN_JSON{{{']]],
                'usage' => ['prompt_tokens' => 320, 'completion_tokens' => 80, 'total_tokens' => 400],
            ], 200),
        ]);

        $chunks = app(LegalRetrievalService::class)->retrieve(
            'Pengurusan KTP elektronik dimintai uang rokok oleh petugas.',
            3
        )['chunks'];
        $analysisService = app(LegalAnalysisService::class);

        // Permintaan pertama pada hari baru (belum ada baris llm_daily_usage) tetap dicek terhadap per_request_token_cap
        $blockedFirst = $analysisService->analyze('Pengurusan KTP-el dimintai pungutan liar.', $chunks);
        Http::assertNothingSent();
        $this->assertSame('fallback', $blockedFirst['meta']['mode']);
        $this->assertSame('quota_exceeded', $blockedFirst['meta']['fallback_reason']);
        $this->assertNotEmpty($blockedFirst['pasal_relevan']);

        // Respons HTTP 200 dengan JSON tidak valid tetap mencatat usage.total_tokens yang telah ditagihkan
        config([
            'llm_quota.daily_token_limit' => 200000,
            'legal_analysis.deepseek.max_retries' => 0,
        ]);

        $invalidJson = $analysisService->analyze('Pengurusan KTP-el dimintai pungutan liar.', $chunks);
        $this->assertSame('fallback', $invalidJson['meta']['mode']);
        $this->assertSame('invalid_json', $invalidJson['meta']['fallback_reason']);
        $this->assertNotEmpty($invalidJson['pasal_relevan']);

        $status = app(LlmQuotaService::class)->status();
        $this->assertSame(400, $status['tokens_used']);
        $this->assertSame(1, $status['consecutive_failures']);
        $this->assertSame(1, $status['logs_summary']['error_count']);
    }
}
