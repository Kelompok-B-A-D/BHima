<?php

namespace Tests\Feature;

use App\Services\LegalAssistantService;
use App\Services\LegalIngestionService;
use App\Services\LegalRetrievalService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConsultationRagTest extends TestCase
{
    use RefreshDatabase;

    private function seedKnowledgeBase(): void
    {
        $this->seed(LegalKnowledgeSeeder::class);
    }

    public function test_consultation_page_renders_and_stores_session_history(): void
    {
        $this->seedKnowledgeBase();

        $this->get('/')->assertOk()->assertSee('Ruang Konsultasi Hukum Warga');
        $this->get('/konsultasi')->assertOk()->assertSee('Asisten Hukum Virtual BHIMA');

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus pembuatan KTP elektronik baru di Disdukcapil, tetapi ada oknum yang meminta uang rokok Rp100.000 agar cepat jadi.',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'answered')
            ->assertJsonStructure([
                'session_id',
                'user_message' => ['id', 'sender', 'content', 'created_at'],
                'assistant_message' => [
                    'id',
                    'sender',
                    'content',
                    'rewritten_query',
                    'citations' => [
                        '*' => ['chunk_id', 'document_key', 'document_name', 'document_number', 'document_year', 'article_label', 'source_url', 'category', 'score', 'excerpt'],
                    ],
                    'retrieval_meta',
                ],
            ]);

        $sessionId = $response->json('session_id');
        $answer = $response->json('assistant_message.content');

        // Pastikan format 4 bagian dan pengingat akhir terpenuhi
        $this->assertStringContainsString('(a) Ringkasan Masalah', $answer);
        $this->assertStringContainsString('(b) Pasal/Aturan yang Relevan dan Artinya', $answer);
        $this->assertStringContainsString('(c) Langkah yang Bisa Dilakukan Warga', $answer);
        $this->assertStringContainsString('(d) Rujukan SOP Layanan', $answer);
        $this->assertStringContainsString('bukan merupakan nasihat hukum resmi', $answer);

        // Pastikan riwayat sesi tersimpan dan dapat diambil kembali
        $history = $this->getJson('/api/konsultasi/riwayat', [
            'X-Consultation-Session' => $sessionId,
        ]);
        $history->assertOk()->assertJsonCount(2, 'messages');

        // Pastikan tautan sumber rujukan dapat diakses
        $firstCitationKey = $response->json('assistant_message.citations.0.document_key');
        $this->get('/rujukan/'.$firstCitationKey)->assertOk();
    }

    public function test_ingestion_indexes_100_documents_and_is_idempotent(): void
    {
        $this->artisan('bhima:ingest-knowledge --seed')->assertExitCode(0);

        $distinctDocs = DB::table('knowledge_chunks')->distinct('document_key')->count('document_key');
        $jdihDocs = DB::table('knowledge_chunks')->where('category', 'JDIH')->distinct('document_key')->count('document_key');
        $sopDocs = DB::table('knowledge_chunks')->where('category', 'SOP')->distinct('document_key')->count('document_key');
        $firstChunkCount = DB::table('knowledge_chunks')->count();
        $firstRecordCount = DB::table('dashboard_records')->whereIn('collection', ['documents', 'contents'])->count();

        $this->assertSame(100, $distinctDocs);
        $this->assertSame(80, $jdihDocs);
        $this->assertSame(20, $sopDocs);
        $this->assertGreaterThan(200, $firstChunkCount);

        // Jalankan ulang proses ingestion untuk memastikan idempotent (tidak menggandakan data)
        $this->artisan('bhima:ingest-knowledge')->assertExitCode(0);
        app(LegalIngestionService::class)->ingestAll();

        $secondChunkCount = DB::table('knowledge_chunks')->count();
        $secondRecordCount = DB::table('dashboard_records')->whereIn('collection', ['documents', 'contents'])->count();

        $this->assertSame($firstChunkCount, $secondChunkCount);
        $this->assertSame($firstRecordCount, $secondRecordCount);
    }

    public function test_retrieval_returns_relevant_top_k_chunks_across_five_distinct_scenarios(): void
    {
        $this->seedKnowledgeBase();
        $retrievalService = app(LegalRetrievalService::class);

        $scenarios = [
            // Skenario 1: Administrasi Kependudukan & Pungli KTP-el
            [
                'chronology' => 'Saya mengurus KTP elektronik yang hilang di kecamatan, sudah bawa KK dan surat kehilangan polisi tapi diminta biaya tambahan uang rokok oleh petugas.',
                'expected_keys' => ['JDIH-001', 'JDIH-004', 'SOP-001', 'SOP-003'],
            ],
            // Skenario 2: Pelayanan Kesehatan Darurat IGD & BPJS
            [
                'chronology' => 'Pasien kecelakaan gawat darurat dibawa ke IGD rumah sakit tetapi ditolak dan diminta membayar uang muka deposit terlebih dahulu sebelum ditangani dokter.',
                'expected_keys' => ['JDIH-006', 'JDIH-007', 'SOP-004'],
            ],
            // Skenario 3: Infrastruktur Jalan Rusak & Kecelakaan
            [
                'chronology' => 'Jalan kabupaten di depan desa kami rusak berlubang parah tanpa rambu peringatan sehingga warga jatuh kecelakaan dan saluran drainase jalan tersumbat banjir.',
                'expected_keys' => ['JDIH-008', 'JDIH-009', 'SOP-005'],
            ],
            // Skenario 4: Ketenagakerjaan (PHK Sepihak, Upah & Penahanan Ijazah)
            [
                'chronology' => 'Perusahaan melakukan PHK sepihak tanpa uang pesangon, gaji bulan ini belum dibayar, dan ijazah asli saya ditahan oleh pemberi kerja.',
                'expected_keys' => ['JDIH-012', 'JDIH-013', 'JDIH-014', 'SOP-007'],
            ],
            // Skenario 5: Sengketa Batas Tanah & Penyerobotan Patok
            [
                'chronology' => 'Tetangga menggeser patok batas bidang tanah pekarangan kami secara sepihak padahal kami memiliki sertifikat hak milik yang sah dari BPN.',
                'expected_keys' => ['JDIH-015', 'JDIH-016', 'SOP-008'],
            ],
        ];

        foreach ($scenarios as $idx => $scenario) {
            $result = $retrievalService->retrieve($scenario['chronology'], 5);

            $this->assertTrue($result['has_context'], "Skenario #{$idx} seharusnya menemukan konteks relevan.");
            $this->assertLessThanOrEqual(5, count($result['chunks']));
            $this->assertNotEmpty($result['chunks']);

            $returnedKeys = array_column($result['chunks'], 'document_key');
            $matched = array_intersect($scenario['expected_keys'], $returnedKeys);

            $this->assertNotEmpty(
                $matched,
                sprintf(
                    'Skenario #%d gagal menemukan dokumen yang diharapkan (%s). Dokumen yang kembali: %s',
                    $idx + 1,
                    implode(', ', $scenario['expected_keys']),
                    implode(', ', $returnedKeys)
                )
            );
        }

        // Pastikan skrip uji artisan juga berjalan sukses untuk seluruh skenario
        $this->artisan('bhima:test-rag')->assertExitCode(0);
    }

    public function test_out_of_scope_chronology_produces_safe_refusal_without_calling_llm(): void
    {
        $this->seedKnowledgeBase();
        Http::fake();

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Bagaimana resep memasak rendang daging sapi khas Padang agar bumbunya meresap dan siapa juara turnamen bulu tangkis minggu depan?',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'out_of_scope')
            ->assertJsonPath('assistant_message.retrieval_meta.llm_called', false)
            ->assertJsonCount(0, 'assistant_message.citations');

        $answer = $response->json('assistant_message.content');
        $this->assertStringContainsString('belum menemukan pasal atau SOP layanan yang cukup relevan', $answer);
        $this->assertStringContainsString('petugas', $answer);

        Http::assertNothingSent();
    }

    public function test_answer_never_predicts_case_outcome_or_gives_verdict(): void
    {
        $this->seedKnowledgeBase();

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Tetangga menyerobot batas tanah pekarangan saya yang sudah bersertifikat SHM. Jika saya lapor dan gugat sekarang, apakah saya pasti menang di pengadilan dan dia pasti dipenjara?',
        ]);

        $response->assertOk()
            ->assertJsonPath('assistant_message.retrieval_meta.guardrail_triggered', true);

        $answer = $response->json('assistant_message.content');

        $this->assertStringContainsString('tidak berwenang memberikan vonis', $answer);
        $this->assertStringContainsString('tidak dapat memastikan menang atau kalah', $answer);
        $this->assertStringNotContainsStringIgnoringCase('Anda pasti menang', $answer);
        $this->assertStringNotContainsStringIgnoringCase('dijamin menang', $answer);
    }

    public function test_all_citations_are_traceable_to_retrieved_chunks_and_hallucinated_citations_are_removed(): void
    {
        $this->seedKnowledgeBase();
        $assistantService = app(LegalAssistantService::class);
        $retrievalService = app(LegalRetrievalService::class);

        $retrieved = $retrievalService->retrieve('Pengurusan KTP elektronik dimintai biaya tambahan uang rokok oleh petugas.', 5);
        $chunks = $retrieved['chunks'];
        $this->assertNotEmpty($chunks);

        // Buat sitasi palsu (tidak ada di retrieved chunks) dan klaim Pasal 999 palsu di teks jawaban
        $candidateCitations = [
            [
                'chunk_id' => $chunks[0]['chunk_id'],
                'document_key' => $chunks[0]['document_key'],
                'article_label' => $chunks[0]['article_label'],
            ],
            [
                'chunk_id' => 999999,
                'document_key' => 'DOKUMEN-PALSU-999',
                'article_label' => 'Pasal 999 Ayat (9)',
            ],
        ];

        $rawAnswer = 'Berdasarkan '.$chunks[0]['article_label'].' layanan ini gratis, sedangkan menurut Pasal 999 warga wajib membayar.';
        $validated = $assistantService->validateCitations($rawAnswer, $candidateCitations, $chunks);

        // Sitasi palsu wajib terbuang, hanya menyisakan sitasi yang benar-benar ada di $chunks
        $this->assertCount(1, $validated['citations']);
        $this->assertSame($chunks[0]['document_key'], $validated['citations'][0]['document_key']);
        $this->assertGreaterThanOrEqual(2, $validated['removed_count']);
        $this->assertStringNotContainsString('Pasal 999', $validated['answer']);
        $this->assertStringContainsString('[Sitasi pasal dihapus karena di luar konteks rujukan]', $validated['answer']);
    }

    public function test_input_sanitization_pii_redaction_and_rate_limiting(): void
    {
        config(['rag.security.rate_limit_per_minute' => 2]);
        $this->seedKnowledgeBase();

        // Kirim kronologi dengan tag HTML berbahaya dan 16 digit NIK pribadi
        $first = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => '<script>alert("xss")</script>Saya warga dengan NIK 3201234567890001 mengurus KTP elektronik namun diminta biaya tambahan Rp50.000.',
        ]);

        $first->assertOk();
        $storedUserContent = $first->json('user_message.content');
        $this->assertStringNotContainsString('<script>', $storedUserContent);
        $this->assertStringNotContainsString('3201234567890001', $storedUserContent);
        $this->assertStringContainsString('[NIK/KK-DISAMARKAN]', $storedUserContent);

        // Permintaan ke-2 masih dalam batas rate limit (2/menit)
        $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Permintaan kedua mengenai prosedur pengurusan Kartu Keluarga di Disdukcapil.',
        ])->assertOk();

        // Permintaan ke-3 melebihi rate limit (429 Too Many Requests)
        $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Permintaan ketiga beruntun yang melebihi batas pengiriman per menit.',
        ])->assertStatus(429);
    }

    public function test_fallback_paragraph_chunking_with_overlap_and_pdf_extraction(): void
    {
        config(['rag.retrieval.chunk_max_chars' => 220, 'rag.retrieval.chunk_overlap_chars' => 50]);
        $ingestion = app(LegalIngestionService::class);

        $longParagraphText = "Prosedur pelayanan pengaduan masyarakat di tingkat kelurahan dilaksanakan secara terbuka dan bebas biaya bagi seluruh warga.\n\n"
            ."Petugas penerima wajib mencatat keluhan warga ke dalam buku register elektronik dan memberikan tanda terima resmi maksimal satu hari kerja.\n\n"
            ."Apabila warga membutuhkan pendampingan khusus, kelurahan mengoordinasikan layanan bersama bagian hukum pemerintah daerah setempat.";

        $chunks = $ingestion->chunkDocumentText($longParagraphText, 'Panduan Kelurahan', 'SOP-KEL-01');

        $this->assertGreaterThanOrEqual(2, count($chunks));
        $this->assertSame('Paragraf 1', $chunks[0]['article_label']);
        $this->assertSame('Paragraf 2', $chunks[1]['article_label']);
    }

    public function test_llm_api_mode_sends_only_chronology_and_chunks_and_handles_timeout_gracefully(): void
    {
        $this->seedKnowledgeBase();
        config([
            'rag.llm.provider' => 'api',
            'rag.llm.api_key' => 'test-secret-key',
            'rag.llm.base_url' => 'https://api.deepseek.test/v1',
        ]);

        Http::fake([
            'https://api.deepseek.test/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => "(a) Ringkasan Masalah\nAnda pasti menang dalam kasus pungli KTP-el ini.\n\n(b) Pasal/Aturan yang Relevan dan Artinya\nBerdasarkan Pasal 79A dan Pasal 999 layanan gratis.\n\n(c) Langkah yang Bisa Dilakukan Warga\nLapor ke BHIMA.\n\n(d) Rujukan SOP Layanan\nSOP-001 dan SOP-003.",
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/konsultasi/pesan', [
            'chronology' => 'Saya mengurus KTP elektronik di Disdukcapil tapi dimintai uang rokok oleh petugas. Apakah saya pasti menang?',
        ]);

        $response->assertOk()->assertJsonPath('assistant_message.retrieval_meta.provider', 'api');
        $answer = $response->json('assistant_message.content');

        // Klaim "Anda pasti menang" dan sitasi luar konteks "Pasal 999" wajib dibersihkan otomatis
        $this->assertStringNotContainsStringIgnoringCase('Anda pasti menang', $answer);
        $this->assertStringNotContainsString('Pasal 999', $answer);
        $this->assertStringContainsString('[Sitasi pasal dihapus karena di luar konteks rujukan]', $answer);
        $this->assertStringContainsString('bukan merupakan nasihat hukum resmi', $answer);

        Http::assertSent(function ($request) {
            $messages = $request['messages'] ?? [];

            return count($messages) === 2
                && $messages[0]['role'] === 'system'
                && str_contains($messages[1]['content'], 'KRONOLOGI WARGA:')
                && str_contains($messages[1]['content'], 'POTONGAN KONTEKS HUKUM & SOP:');
        });
    }

    public function test_session_reset_and_input_validation_errors(): void
    {
        $this->postJson('/api/konsultasi/pesan', ['chronology' => ' pendek '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('chronology');

        $reset = $this->postJson('/api/konsultasi/reset');
        $reset->assertOk()->assertJsonStructure(['session_id', 'message']);
    }
}

