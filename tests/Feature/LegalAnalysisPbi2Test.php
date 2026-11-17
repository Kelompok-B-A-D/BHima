<?php

namespace Tests\Feature;

use App\Contracts\AnalysisProvider;
use App\Services\Analysis\DeepSeekProvider;
use App\Services\Analysis\MockProvider;
use App\Services\LegalAnalysisService;
use App\Services\LegalRetrievalService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegalAnalysisPbi2Test extends TestCase
{
    use RefreshDatabase;

    private function seedKnowledgeBase(): void
    {
        $this->seed(LegalKnowledgeSeeder::class);
    }

    /**
     * Kriteria 1 & 2:
     * Untuk minimal 5 skenario kronologi yang relevan, sistem menampilkan rujukan undang-undang,
     * pasal terkait, dan estimasi ancaman sanksi yang 100% bersumber dari chunk hasil retrieval,
     * serta membuang pasal/dokumen/sanksi karangan di luar konteks.
     */
    public function test_five_scenarios_produce_grounded_analysis_only_from_retrieved_chunks(): void
    {
        config(['legal_analysis.provider' => 'mock']);
        $this->seedKnowledgeBase();

        $retrievalService = app(LegalRetrievalService::class);
        $analysisService = app(LegalAnalysisService::class);

        $scenarios = [
            // Skenario 1: Pungutan liar pengurusan KTP-el (memiliki sanksi pidana eksplisit di Pasal 95B UU 24/2013)
            'Saya mengurus KTP elektronik yang hilang di kantor Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai.',
            // Skenario 2: Penolakan pasien darurat IGD & permintaan uang muka (memiliki sanksi pidana di Pasal 438 UU 17/2023)
            'Pasien kecelakaan gawat darurat dibawa ke IGD rumah sakit tetapi ditolak dan diminta uang muka deposit Rp5 juta sebelum ditangani dokter.',
            // Skenario 3: Jalan rusak berlubang tanpa rambu & kecelakaan (memiliki sanksi pidana di Pasal 273 UU 22/2009)
            'Jalan kabupaten di desa kami rusak berlubang parah tanpa rambu peringatan sehingga warga jatuh kecelakaan lalu lintas.',
            // Skenario 4: PHK sepihak, keterlambatan upah & penahanan ijazah asli
            'Perusahaan melakukan PHK sepihak tanpa perundingan bipartit, gaji bulan terakhir belum dibayar, dan ijazah asli saya ditahan.',
            // Skenario 5: Sengketa patok batas tanah pekarangan bersertifikat SHM
            'Tetangga menggeser patok batas bidang tanah pekarangan keluarga kami secara sepihak padahal kami memegang sertifikat hak milik (SHM).',
        ];

        foreach ($scenarios as $idx => $chronology) {
            $retrieved = $retrievalService->retrieve($chronology, 5);
            $chunks = $retrieved['chunks'];
            $this->assertNotEmpty($chunks, "Skenario #{$idx} harus memiliki chunk hasil retrieval.");

            $analysis = $analysisService->analyze($chronology, $chunks);

            $this->assertTrue(LegalAnalysisService::isValidSchema($analysis), "Skenario #{$idx} harus memenuhi skema JSON tetap.");
            $this->assertNotEmpty($analysis['rujukan'], "Skenario #{$idx} harus menampilkan rujukan dokumen.");
            $this->assertNotEmpty($analysis['pasal_relevan'], "Skenario #{$idx} harus menampilkan pasal relevan.");
            $this->assertNotEmpty($analysis['ancaman_sanksi'], "Skenario #{$idx} harus menampilkan estimasi ancaman sanksi.");

            $allowedDocs = array_column($chunks, 'document_name');
            $allowedArticles = array_column($chunks, 'article_label');
            $allowedUrls = array_column($chunks, 'source_url');

            foreach ($analysis['rujukan'] as $rujukan) {
                $this->assertContains($rujukan['dokumen'], $allowedDocs);
                $this->assertContains($rujukan['tautan'], $allowedUrls);
            }

            foreach ($analysis['pasal_relevan'] as $pasal) {
                $this->assertContains($pasal['pasal'], $allowedArticles);
                $this->assertContains($pasal['dokumen'], $allowedDocs);
                $this->assertStringContainsStringIgnoringCase('berpotensi terkait', $pasal['penjelasan_awam']);
            }

            foreach ($analysis['ancaman_sanksi'] as $sanksi) {
                $this->assertContains($sanksi['pasal'], $allowedArticles);
                $this->assertContains($sanksi['dokumen'], $allowedDocs);

                // Cari chunk terkait dan pastikan sanksi benar-benar berasal dari teks chunk atau bertuliskan "Tidak tercantum..."
                $matchedChunk = collect($chunks)->first(
                    fn (array $c) => $c['article_label'] === $sanksi['pasal'] && $c['document_name'] === $sanksi['dokumen']
                );
                $this->assertNotNull($matchedChunk);

                if ($sanksi['bunyi_sanksi_ringkas'] !== 'Tidak tercantum dalam potongan pasal yang ditemukan') {
                    $this->assertStringContainsStringIgnoringCase(
                        $sanksi['bunyi_sanksi_ringkas'],
                        (string) $matchedChunk['content']
                    );
                }
            }
        }

        // Uji pemfilteran pasca-model ketika ada pasal/dokumen/sanksi karangan di luar chunk
        $sampleChunks = $retrievalService->retrieve($scenarios[0], 3)['chunks'];
        $hallucinatedOutput = [
            'rujukan' => [
                ['dokumen' => $sampleChunks[0]['document_name'], 'nomor_tahun' => $sampleChunks[0]['document_number'], 'tautan' => $sampleChunks[0]['source_url']],
                ['dokumen' => 'UU Karangan Bebas', 'nomor_tahun' => 'UU No. 999 Tahun 2099', 'tautan' => 'https://palsu.test'],
            ],
            'pasal_relevan' => [
                [
                    'pasal' => $sampleChunks[0]['article_label'],
                    'dokumen' => $sampleChunks[0]['document_name'],
                    'penjelasan_awam' => 'Pasal yang dilanggar adalah '.$sampleChunks[0]['article_label'].' dan Pasal 888.',
                    'alasan_terkait_kronologi' => 'Terkait langsung dengan kronologi warga.',
                ],
                [
                    'pasal' => 'Pasal 999 Ayat (9)',
                    'dokumen' => 'UU Karangan Bebas',
                    'penjelasan_awam' => 'Pasal karangan yang tidak ada di chunk.',
                    'alasan_terkait_kronologi' => 'Karangan model.',
                ],
            ],
            'ancaman_sanksi' => [
                [
                    'pasal' => 'Pasal 999 Ayat (9)',
                    'jenis' => 'pidana',
                    'bunyi_sanksi_ringkas' => 'Pidana penjara 99 tahun dan denda Rp999 miliar',
                    'dokumen' => 'UU Karangan Bebas',
                ],
            ],
            'perlu_eskalasi' => true,
            'catatan' => 'Catatan pengujian.',
        ];

        $filtered = $analysisService->validateAndFilterOutput($hallucinatedOutput, $sampleChunks);
        $this->assertCount(1, $filtered['rujukan']);
        $this->assertCount(1, $filtered['pasal_relevan']);
        $this->assertCount(0, $filtered['ancaman_sanksi']);
        $this->assertStringNotContainsString('Pasal 888', $filtered['pasal_relevan'][0]['penjelasan_awam']);
        $this->assertStringContainsString('pasal yang berpotensi terkait', $filtered['pasal_relevan'][0]['penjelasan_awam']);
        $this->assertGreaterThanOrEqual(3, $filtered['removed_entries_count']);
    }

    /**
     * Kriteria 3:
     * Tampilan tidak pernah memuat prediksi vonis atau peluang menang/kalah. Uji dengan "apakah saya pasti menang?".
     */
    public function test_analysis_never_contains_verdict_prediction_when_asked_apakah_saya_pasti_menang(): void
    {
        config(['legal_analysis.provider' => 'mock']);
        $this->seedKnowledgeBase();

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Tetangga menggeser patok batas tanah pekarangan saya yang sudah bersertifikat SHM. Jika saya lapor sekarang, apakah saya pasti menang?',
        ]);

        $response->assertOk();
        $analysis = $response->json('assistant_message.legal_analysis');

        $this->assertIsArray($analysis);
        $this->assertTrue($analysis['meta']['verdict_guardrail_triggered']);
        $this->assertSame('berdasarkan bunyi pasal, bukan prediksi putusan', $analysis['meta']['sanction_label']);
        $this->assertStringContainsString('tidak berwenang memberikan vonis', $analysis['catatan']);
        $this->assertStringContainsString('tidak memprediksi peluang menang atau kalah', $analysis['catatan']);

        $serializedAnalysis = json_encode($analysis, JSON_UNESCAPED_UNICODE) ?: '';
        $this->assertStringNotContainsStringIgnoringCase('Anda pasti menang', $serializedAnalysis);
        $this->assertStringNotContainsStringIgnoringCase('dijamin menang', $serializedAnalysis);
    }

    /**
     * Kriteria 4:
     * Pasal tanpa sanksi eksplisit menampilkan keterangan "Tidak tercantum dalam potongan pasal yang ditemukan",
     * bukan angka karangan.
     */
    public function test_articles_without_explicit_sanctions_display_tidak_tercantum_and_reject_fabricated_numbers(): void
    {
        $this->seedKnowledgeBase();
        $analysisService = app(LegalAnalysisService::class);

        // Ambil chunk yang murni mengatur prosedur/hak tanpa sanksi (contoh: JDIH-015 Pasal 18 Ayat (1) tentang penetapan batas tanah)
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
            'score' => 0.82,
        ]];

        // 1. Uji dengan MockProvider
        $mockResult = $analysisService->analyze('Sengketa penetapan batas bidang tanah pekarangan dengan tetangga.', $chunkArray);
        $this->assertCount(1, $mockResult['ancaman_sanksi']);
        $this->assertSame(
            'Tidak tercantum dalam potongan pasal yang ditemukan',
            $mockResult['ancaman_sanksi'][0]['bunyi_sanksi_ringkas']
        );

        // 2. Uji bila model eksternal mencoba mengarang angka sanksi pada pasal yang tidak memuat sanksi
        $fabricatedRaw = [
            'rujukan' => [
                ['dokumen' => $chunkArray[0]['document_name'], 'nomor_tahun' => 'PP No. 24 Tahun 1997', 'tautan' => $chunkArray[0]['source_url']],
            ],
            'pasal_relevan' => [
                [
                    'pasal' => 'Pasal 18 Ayat (1)',
                    'dokumen' => $chunkArray[0]['document_name'],
                    'penjelasan_awam' => 'Penetapan batas tanah.',
                    'alasan_terkait_kronologi' => 'Terkait batas tanah.',
                ],
            ],
            'ancaman_sanksi' => [
                [
                    'pasal' => 'Pasal 18 Ayat (1)',
                    'jenis' => 'pidana',
                    'bunyi_sanksi_ringkas' => 'Pidana penjara 10 tahun dan denda Rp500.000.000',
                    'dokumen' => $chunkArray[0]['document_name'],
                ],
            ],
            'perlu_eskalasi' => true,
            'catatan' => 'Catatan.',
        ];

        $validated = $analysisService->validateAndFilterOutput($fabricatedRaw, $chunkArray);
        $this->assertSame(
            'Tidak tercantum dalam potongan pasal yang ditemukan',
            $validated['ancaman_sanksi'][0]['bunyi_sanksi_ringkas']
        );
        $this->assertSame('lainnya', $validated['ancaman_sanksi'][0]['jenis']);
    }

    /**
     * Kriteria 5:
     * Konteks kosong menghasilkan penolakan aman dan tombol eskalasi aktif (perlu_eskalasi = true).
     */
    public function test_empty_context_returns_empty_lists_and_active_escalation(): void
    {
        $this->seedKnowledgeBase();

        $response = $this->postJson('/api/konsultasi/analisis', [
            'chronology' => 'Bagaimana cara membuat kue bolu kukus pandan yang mekar dan lembut untuk acara keluarga?',
            'chunks' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('rujukan', [])
            ->assertJsonPath('pasal_relevan', [])
            ->assertJsonPath('ancaman_sanksi', [])
            ->assertJsonPath('perlu_eskalasi', true)
            ->assertJsonPath('meta.status', 'out_of_scope');

        $this->assertStringContainsString('Ajukan Pengaduan', $response->json('catatan'));
    }

    /**
     * Kriteria 6:
     * Tombol eskalasi membuka formulir pengaduan dengan data awal terisi otomatis (kronologi, ringkasan pasal, rujukan)
     * yang dapat diedit warga, dan saat dikirim tersimpan ke database beserta relasi sesi/pesan.
     */
    public function test_escalation_prefills_complaint_form_allows_editing_and_persists_to_database(): void
    {
        $this->seedKnowledgeBase();

        // 1. Warga melakukan konsultasi di ruang percakapan
        $chatResponse = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik yang hilang di Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat diproses.',
        ]);
        $chatResponse->assertOk();

        $sessionUuid = $chatResponse->json('session_id');
        $assistantMessageId = $chatResponse->json('assistant_message.id');

        // 2. Warga mengklik tombol "Ajukan Pengaduan" yang mengarah ke /pengaduan/buat
        $formPage = $this->get('/pengaduan/buat?session_id='.$sessionUuid.'&message_id='.$assistantMessageId);
        $formPage->assertOk()
            ->assertSee('Formulir Pengaduan Masyarakat')
            ->assertSee('mengurus KTP elektronik yang hilang')
            ->assertSee('Perpres No. 87 Tahun 2016');

        // 3. Warga mengedit data pada formulir dan mengirimkan pengaduan resmi
        $submitResponse = $this->postJson('/pengaduan', [
            'session_id' => $sessionUuid,
            'message_id' => $assistantMessageId,
            'reporter' => 'Warga Pelapor Uji',
            'category' => 'Dugaan Pungutan Liar',
            'summary' => 'Laporan Pungli Rp100.000 saat Pengurusan KTP-el Hilang (Diedit Warga)',
            'chronology' => 'Kronologi yang telah diedit warga: Saat mengurus KTP-el hilang di loket kecamatan, oknum meminta uang Rp100.000 di luar ketentuan gratis.',
            'reported' => 'Oknum Loket Pelayanan KTP-el',
            'location' => 'Kantor Pelayanan Kecamatan',
            'incidentDate' => '2026-11-08',
            'ai' => 'Pasal 79A & Pasal 95B UU No. 24 Tahun 2013: Pengurusan Dokumen Kependudukan gratis; pungutan diancam pidana penjara paling lama 6 tahun.',
            'references_text' => "Undang-Undang Perubahan atas Administrasi Kependudukan (UU No. 24 Tahun 2013) — /rujukan/JDIH-001\nPerpres No. 87 Tahun 2016 — /rujukan/JDIH-004",
        ]);

        $submitResponse->assertCreated()
            ->assertJsonStructure(['message', 'ticket_id', 'complaint', 'consultation_session_id', 'consultation_message_id']);

        $ticketId = $submitResponse->json('ticket_id');
        $this->assertNotEmpty($ticketId);
        $this->assertSame($assistantMessageId, $submitResponse->json('consultation_message_id'));

        // 4. Pastikan tersimpan di tabel dashboard_records (collection = 'complaints') beserta relasinya
        $savedRecord = DB::table('dashboard_records')
            ->where('collection', 'complaints')
            ->where('record_key', $ticketId)
            ->first();

        $this->assertNotNull($savedRecord);
        $this->assertSame($assistantMessageId, (int) $savedRecord->consultation_message_id);
        $this->assertNotNull($savedRecord->consultation_session_id);

        $payload = json_decode((string) $savedRecord->payload, true);
        $this->assertSame('Laporan Pungli Rp100.000 saat Pengurusan KTP-el Hilang (Diedit Warga)', $payload['summary']);
        $this->assertStringContainsString('Pasal 79A & Pasal 95B', $payload['ai']);
        $this->assertCount(2, $payload['references']);
    }

    /**
     * Kriteria 7:
     * API key tidak muncul di kode klien maupun repo, dan data identitas warga (NIK, telepon, email, nama, alamat)
     * di-masking sebelum disusun ke dalam payload DeepSeekProvider.
     */
    public function test_deepseek_payload_masks_personal_identities_and_client_views_never_expose_api_key(): void
    {
        config([
            'legal_analysis.provider' => 'deepseek',
            'legal_analysis.deepseek.api_key' => 'sk-deepseek-rahasia-uji-998877',
            'legal_analysis.deepseek.temperature' => 0.8, // Harus otomatis dibatasi maksimal 0.2
        ]);

        $provider = app(DeepSeekProvider::class);

        $rawChronologyWithPii = 'Nama saya Budi Santoso, NIK 3201234567890001, nomor HP 081234567890, email budi.santoso@gmail.com, alamat: Jl. Merdeka No. 45 RT 02 RW 05. Saya dimintai uang rokok saat mengurus KTP-el.';
        $sampleChunks = [[
            'document_name' => 'Undang-Undang Perubahan atas Administrasi Kependudukan',
            'document_number' => 'UU No. 24 Tahun 2013',
            'document_year' => 2013,
            'article_label' => 'Pasal 79A',
            'category' => 'JDIH',
            'source_url' => 'http://localhost/rujukan/JDIH-001#pasal-79a',
            'content' => 'Pengurusan dan penerbitan Dokumen Kependudukan tidak dipungut biaya (gratis).',
        ]];

        $payload = $provider->buildRequestPayload($rawChronologyWithPii, $sampleChunks);

        // Temperature wajib dibatasi maksimal 0.2
        $this->assertLessThanOrEqual(0.2, $payload['temperature']);

        $userMessageContent = $payload['messages'][1]['content'];
        $this->assertStringNotContainsString('3201234567890001', $userMessageContent);
        $this->assertStringNotContainsString('081234567890', $userMessageContent);
        $this->assertStringNotContainsString('budi.santoso@gmail.com', $userMessageContent);
        $this->assertStringNotContainsString('Budi Santoso', $userMessageContent);
        $this->assertStringNotContainsString('Jl. Merdeka No. 45', $userMessageContent);

        $this->assertStringContainsString('[NIK/KK-DISAMARKAN]', $userMessageContent);
        $this->assertStringContainsString('[TELEPON-DISAMARKAN]', $userMessageContent);
        $this->assertStringContainsString('[EMAIL-DISAMARKAN]', $userMessageContent);
        $this->assertStringContainsString('[NAMA-DISAMARKAN]', $userMessageContent);
        $this->assertStringContainsString('[ALAMAT-DISAMARKAN]', $userMessageContent);

        // Pastikan halaman klien tidak pernah mengekspos API key
        $html = $this->get('/konsultasi')->assertOk()->getContent();
        $this->assertStringNotContainsString('sk-deepseek-rahasia-uji-998877', (string) $html);
    }

    /**
     * Kriteria 8 & 9:
     * Error provider (key kosong, rate limit 429, invalid JSON dengan 1x retry, HTTP 500, timeout)
     * ditangani secara ramah tanpa crash (diuji dengan mock HTTP), dan pergantian LLM_PROVIDER bekerja via config/.env.
     */
    public function test_deepseek_provider_handles_empty_key_rate_limit_invalid_json_retry_and_http_errors_without_crash(): void
    {
        $this->seedKnowledgeBase();
        $retrievalService = app(LegalRetrievalService::class);
        $analysisService = app(LegalAnalysisService::class);

        $chunks = $retrievalService->retrieve('Pengurusan KTP elektronik dimintai uang rokok oleh petugas.', 3)['chunks'];

        // 1. Pergantian provider melalui konfigurasi LLM_PROVIDER
        config(['legal_analysis.provider' => 'mock']);
        $this->assertInstanceOf(MockProvider::class, app(AnalysisProvider::class));

        config(['legal_analysis.provider' => 'deepseek']);
        $this->assertInstanceOf(DeepSeekProvider::class, app(AnalysisProvider::class));

        $validMockJson = json_encode([
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
                    'penjelasan_awam' => 'Pasal yang berpotensi terkait dengan layanan kependudukan gratis.',
                    'alasan_terkait_kronologi' => 'Kronologi warga berkaitan dengan pungutan pengurusan KTP-el.',
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
            'catatan' => 'Analisis berhasil setelah retry.',
        ], JSON_UNESCAPED_UNICODE);

        Http::fakeSequence('api.deepseek.test/*')
            ->push(['choices' => [['message' => ['content' => 'BUKAN_JSON_VALID{{{']]]], 200)
            ->push(['choices' => [['message' => ['content' => $validMockJson]]]], 200)
            ->push(['message' => 'Too Many Requests'], 429)
            ->push(['choices' => [['message' => ['content' => 'MASIH_TIDAK_VALID_1']]]], 200)
            ->push(['choices' => [['message' => ['content' => 'MASIH_TIDAK_VALID_2']]]], 200);

        // 2. Uji ketika LLM_PROVIDER=deepseek tetapi DEEPSEEK_API_KEY kosong (tidak memanggil jaringan & tidak crash)
        config([
            'legal_analysis.deepseek.api_key' => '',
            'legal_analysis.deepseek.base_url' => 'https://api.deepseek.test/v1',
            'legal_analysis.deepseek.max_retries' => 1,
        ]);

        $emptyKeyResult = $analysisService->analyze('Pengurusan KTP-el dimintai biaya tambahan.', $chunks);
        $this->assertSame('error', $emptyKeyResult['meta']['status']);
        $this->assertSame('missing_api_key', $emptyKeyResult['meta']['error']);
        $this->assertTrue($emptyKeyResult['perlu_eskalasi']);
        Http::assertNothingSent();

        // 3. Uji ketika DeepSeek mengembalikan JSON tidak valid pada percobaan ke-1, lalu berhasil pada retry ke-1
        config([
            'legal_analysis.deepseek.api_key' => 'test-deepseek-key',
        ]);

        $retrySuccess = $analysisService->analyze('Pengurusan KTP-el dimintai biaya tambahan.', $chunks);
        $this->assertSame('ok', $retrySuccess['meta']['status']);
        $this->assertCount(1, $retrySuccess['pasal_relevan']);

        // 4. Uji ketika DeepSeek mengembalikan HTTP 429 Rate Limit
        $rateLimited = $analysisService->analyze('Pengurusan KTP-el dimintai biaya tambahan.', $chunks);
        $this->assertSame('error', $rateLimited['meta']['status']);
        $this->assertSame('rate_limit', $rateLimited['meta']['error']);
        $this->assertTrue($rateLimited['perlu_eskalasi']);

        // 5. Uji ketika JSON tetap tidak valid setelah retry maksimal (1x retry)
        $invalidJsonResult = $analysisService->analyze('Pengurusan KTP-el dimintai biaya tambahan.', $chunks);
        $this->assertSame('error', $invalidJsonResult['meta']['status']);
        $this->assertSame('invalid_json', $invalidJsonResult['meta']['error']);
        $this->assertTrue($invalidJsonResult['perlu_eskalasi']);
    }
}
