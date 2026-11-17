<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LegalAssistantService
{
    /**
     * Pola pertanyaan yang meminta vonis, jaminan kemenangan, atau prediksi hasil perkara.
     *
     * @var list<string>
     */
    private const VERDICT_PATTERNS = [
        'pasti menang',
        'dijamin menang',
        'bisa menang',
        'peluang menang',
        'persen menang',
        'pasti kalah',
        'apakah saya menang',
        'apakah kami menang',
        'jaminan menang',
        'vonis hakim',
        'putuskan bersalah',
        'pasti dipenjara',
        'pasti dihukum',
        'berapa tahun penjara yang akan dijatuhkan',
        'menjamin hasil',
    ];

    /**
     * Pola kalimat terlarang pada output (klaim kemenangan/vonis).
     *
     * @var list<string>
     */
    private const PROHIBITED_OUTPUT_PATTERNS = [
        '/anda\s+pasti\s+menang/iu',
        '/dijamin\s+menang/iu',
        '/dipastikan\s+menang/iu',
        '/anda\s+pasti\s+kalah/iu',
        '/pihak\s+lawan\s+pasti\s+kalah/iu',
        '/pasti\s+divonis\s+bersalah/iu',
        '/saya\s+memvonis/iu',
        '/kami\s+menjamin\s+anda\s+menang/iu',
    ];

    public function __construct(
        private readonly LegalRetrievalService $retrievalService
    ) {}

    /**
     * Jalankan alur RAG lengkap untuk kronologi warga:
     * Retrieval -> Cek Ambang -> Generasi Jawaban -> Validasi Sitasi & Guardrail.
     *
     * @return array{
     *     status: string,
     *     answer: string,
     *     rewritten_query: string,
     *     citations: list<array<string, mixed>>,
     *     retrieval_meta: array<string, mixed>
     * }
     */
    public function answerChronology(string $chronology, ?int $topK = null, ?float $minSimilarity = null): array
    {
        $sanitizedChronology = $this->sanitizeChronology($chronology);
        $verdictRequested = $this->detectVerdictRequest($sanitizedChronology);

        $retrieval = $this->retrievalService->retrieve($sanitizedChronology, $topK, $minSimilarity);
        $chunks = $retrieval['chunks'];

        // Bila tidak ada chunk di atas ambang kemiripan, JANGAN memanggil LLM untuk substansi hukum
        if (! $retrieval['has_context'] || empty($chunks)) {
            $outOfScopeMessage = (string) config('rag.out_of_scope_message');
            $disclaimer = (string) config('rag.disclaimer');

            $parts = [];
            if ($verdictRequested) {
                $parts[] = (string) config('rag.verdict_guardrail_notice');
            }
            $parts[] = $outOfScopeMessage;
            $parts[] = $disclaimer;

            return [
                'status' => 'out_of_scope',
                'answer' => implode("\n\n", $parts),
                'rewritten_query' => $retrieval['rewritten_query'],
                'citations' => [],
                'retrieval_meta' => [
                    'status' => 'out_of_scope',
                    'llm_called' => false,
                    'top_score' => $retrieval['top_score'],
                    'threshold' => $retrieval['threshold'],
                    'matched_count' => 0,
                    'guardrail_triggered' => $verdictRequested,
                ],
            ];
        }

        // Kirim ke LLM hanya kronologi warga dan chunk hasil retrieval
        $generation = $this->generateFromContext($sanitizedChronology, $chunks, $verdictRequested);

        // Validasi semua sitasi terhadap chunk yang benar-benar diambil
        $validated = $this->validateCitations(
            $generation['answer'],
            $generation['citations'],
            $chunks
        );

        // Pastikan tidak ada bahasa vonis/prediksi dan diakhiri kalimat pengingat
        $finalAnswer = $this->enforceOutputGuardrails($validated['answer'], $verdictRequested);

        return [
            'status' => 'answered',
            'answer' => $finalAnswer,
            'rewritten_query' => $retrieval['rewritten_query'],
            'citations' => $validated['citations'],
            'retrieval_meta' => [
                'status' => 'answered',
                'llm_called' => $generation['llm_called'],
                'provider' => $generation['provider'],
                'top_score' => $retrieval['top_score'],
                'threshold' => $retrieval['threshold'],
                'matched_count' => count($chunks),
                'guardrail_triggered' => $verdictRequested,
            ],
        ];
    }

    /**
     * Sanitasi input kronologi warga (hapus tag HTML dan karakter kontrol).
     */
    public function sanitizeChronology(string $input): string
    {
        $clean = strip_tags($input);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean) ?? '';
        $clean = trim(preg_replace('/\s+/u', ' ', $clean) ?? '');

        $maxLen = (int) config('rag.security.max_chronology_length', 4000);

        return mb_substr($clean, 0, $maxLen);
    }

    /**
     * Deteksi apakah warga menanyakan prediksi menang/kalah atau meminta vonis perkara.
     */
    public function detectVerdictRequest(string $chronology): bool
    {
        $lower = mb_strtolower($chronology, 'UTF-8');
        foreach (self::VERDICT_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return (bool) preg_match('/\b(apakah|bisakah|mungkinkah)\b.*\b(pasti\s+menang|menang\s+sidang|menang\s+perkara|divonis\s+bebas)\b/iu', $lower);
    }

    /**
     * Validasi output: pastikan setiap sitasi merujuk ke chunk yang benar-benar diambil.
     * Buang sitasi yang tidak ada di konteks, dan tandai/bersihkan klaim pasal di luar konteks pada teks.
     *
     * @param  list<array<string, mixed>>  $candidateCitations
     * @param  list<array<string, mixed>>  $retrievedChunks
     * @return array{answer: string, citations: list<array<string, mixed>>, removed_count: int}
     */
    public function validateCitations(string $answerText, array $candidateCitations, array $retrievedChunks): array
    {
        $byKey = [];
        $byChunkId = [];
        $allowedNumbers = [];
        $allowedArticles = [];

        foreach ($retrievedChunks as $chunk) {
            $docKey = (string) $chunk['document_key'];
            $byKey[$docKey][] = $chunk;
            $byChunkId[(int) $chunk['chunk_id']] = $chunk;
            $allowedNumbers[] = mb_strtolower((string) $chunk['document_number'], 'UTF-8');
            if (preg_match('/Pasal\s+(\d+[A-Z]?)/iu', (string) $chunk['article_label'], $m)) {
                $allowedArticles[] = mb_strtolower($m[1], 'UTF-8');
            }
        }

        $verifiedCitations = [];
        $seenChunkIds = [];
        $removedCount = 0;

        foreach ($candidateCitations as $cand) {
            $matchedChunk = null;

            if (isset($cand['chunk_id']) && isset($byChunkId[(int) $cand['chunk_id']])) {
                $matchedChunk = $byChunkId[(int) $cand['chunk_id']];
            } elseif (! empty($cand['document_key']) && isset($byKey[(string) $cand['document_key']])) {
                $candidatesForKey = $byKey[(string) $cand['document_key']];
                $candArticle = mb_strtolower((string) ($cand['article_label'] ?? ''), 'UTF-8');
                foreach ($candidatesForKey as $c) {
                    if ($candArticle !== '' && mb_strtolower((string) $c['article_label'], 'UTF-8') === $candArticle) {
                        $matchedChunk = $c;
                        break;
                    }
                }
                $matchedChunk ??= $candidatesForKey[0];
            }

            if ($matchedChunk === null) {
                $removedCount++;

                continue;
            }

            $cid = (int) $matchedChunk['chunk_id'];
            if (isset($seenChunkIds[$cid])) {
                continue;
            }
            $seenChunkIds[$cid] = true;

            $verifiedCitations[] = $this->formatCitationFromChunk($matchedChunk);
        }

        // Jika LLM tidak mengembalikan array sitasi terstruktur yang valid, bangun dari chunk yang dirujuk di jawaban
        if (empty($verifiedCitations) && ! empty($retrievedChunks)) {
            foreach ($retrievedChunks as $chunk) {
                $verifiedCitations[] = $this->formatCitationFromChunk($chunk);
            }
        }

        // Periksa penyebutan Pasal di teks jawaban yang tidak ada pada chunk yang diambil
        $cleanedAnswer = preg_replace_callback(
            '/\bPasal\s+(\d+[A-Z]?)\b/iu',
            function (array $matches) use ($allowedArticles, &$removedCount) {
                $num = mb_strtolower($matches[1], 'UTF-8');
                if (in_array($num, $allowedArticles, true)) {
                    return $matches[0];
                }
                $removedCount++;

                return '[Sitasi pasal dihapus karena di luar konteks rujukan]';
            },
            $answerText
        ) ?? $answerText;

        return [
            'answer' => $cleanedAnswer,
            'citations' => $verifiedCitations,
            'removed_count' => $removedCount,
        ];
    }

    /**
     * Kirim kronologi warga dan potongan konteks ke LLM (jika dikonfigurasi) atau penyusun jawaban berbasis konteks.
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array{answer: string, citations: list<array<string, mixed>>, llm_called: bool, provider: string}
     */
    private function generateFromContext(string $chronology, array $chunks, bool $verdictRequested): array
    {
        $provider = (string) config('rag.llm.provider', 'local_grounded');
        $apiKey = (string) config('rag.llm.api_key', '');
        $systemPrompt = (string) config('rag.system_prompt');

        // Format konteks yang dikirim ke LLM: HANYA kronologi warga dan chunk hasil retrieval
        $contextBlocks = [];
        foreach ($chunks as $idx => $chunk) {
            $num = $idx + 1;
            $contextBlocks[] = sprintf(
                "[SUMBER #%d]\nID Dokumen: %s\nKategori: %s\nNama Dokumen: %s\nJenis: %s\nNomor/Tahun: %s (%d)\nPasal/Bagian: %s\nTautan Sumber: %s\nIsi Konteks: %s",
                $num,
                $chunk['document_key'],
                $chunk['category'],
                $chunk['document_name'],
                $chunk['document_type'],
                $chunk['document_number'],
                $chunk['document_year'],
                $chunk['article_label'],
                $chunk['source_url'],
                $chunk['content']
            );
        }

        $userPrompt = "KRONOLOGI WARGA:\n".$chronology."\n\nPOTONGAN KONTEKS HUKUM & SOP:\n".implode("\n\n", $contextBlocks);

        if ($provider === 'api' && $apiKey !== '') {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout((int) config('rag.llm.timeout', 20))
                    ->post(rtrim((string) config('rag.llm.base_url'), '/').'/chat/completions', [
                        'model' => config('rag.llm.model', 'deepseek-chat'),
                        'temperature' => (float) config('rag.llm.temperature', 0.1),
                        'max_tokens' => (int) config('rag.llm.max_tokens', 1200),
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $userPrompt],
                        ],
                    ]);

                if ($response->successful()) {
                    $llmText = trim((string) $response->json('choices.0.message.content', ''));
                    if ($llmText !== '') {
                        return [
                            'answer' => $llmText,
                            'citations' => array_map(fn (array $c) => $this->formatCitationFromChunk($c), $chunks),
                            'llm_called' => true,
                            'provider' => 'api',
                        ];
                    }
                }

                Log::error('LLM API mengembalikan status gagal saat generasi jawaban konsultasi.', [
                    'status' => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Koneksi LLM API gagal atau timeout saat generasi jawaban konsultasi.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Penyusunan jawaban terstruktur berbasis konteks (Context-Grounded Synthesis)
        $synthesized = $this->synthesizeGroundedAnswer($chronology, $chunks, $verdictRequested);

        return [
            'answer' => $synthesized,
            'citations' => array_map(fn (array $c) => $this->formatCitationFromChunk($c), $chunks),
            'llm_called' => true,
            'provider' => 'local_grounded',
        ];
    }

    /**
     * Susun jawaban bahasa awam 4 bagian yang 100% bersumber dari chunk hasil retrieval.
     *
     * @param  list<array<string, mixed>>  $chunks
     */
    private function synthesizeGroundedAnswer(string $chronology, array $chunks, bool $verdictRequested): string
    {
        $jdihChunks = array_values(array_filter($chunks, fn (array $c) => $c['category'] === 'JDIH'));
        $sopChunks = array_values(array_filter($chunks, fn (array $c) => $c['category'] === 'SOP'));
        $primaryTopic = $chunks[0]['topic'] ?? 'Layanan Hukum dan Masyarakat';

        // (a) Ringkasan masalah dalam 1-2 kalimat
        $shortChronology = mb_strlen($chronology) > 160
            ? mb_substr($chronology, 0, 157).'...'
            : $chronology;

        $summarySection = "(a) Ringkasan Masalah\n"
            ."Berdasarkan kronologi yang Anda sampaikan (\"{$shortChronology}\"), permasalahan ini berkaitan dengan bidang {$primaryTopic}. ";
        if ($verdictRequested) {
            $summarySection .= (string) config('rag.verdict_guardrail_notice');
        } else {
            $summarySection .= 'Berikut adalah ketentuan aturan dan prosedur layanan resmi yang relevan dengan kondisi Anda.';
        }

        // (b) Pasal/aturan yang relevan dan artinya dalam bahasa awam
        $rulesLines = ['(b) Pasal/Aturan yang Relevan dan Artinya'];
        $targetRuleChunks = ! empty($jdihChunks) ? $jdihChunks : $chunks;

        foreach ($targetRuleChunks as $chunk) {
            $plainExplanation = $this->explainLegalTermsInPlainLanguage((string) $chunk['content']);
            $citationInline = sprintf(
                '[Sitasi: %s (%s Tahun %d), %s — Sumber: %s]',
                $chunk['document_name'],
                $chunk['document_number'],
                $chunk['document_year'],
                $chunk['article_label'],
                $chunk['source_url']
            );
            $rulesLines[] = sprintf(
                '- Berdasarkan %s (%s), %s: %s %s %s',
                $chunk['document_name'],
                $chunk['document_number'],
                $chunk['article_label'],
                $chunk['content'],
                $plainExplanation,
                $citationInline
            );
        }

        // (c) Langkah yang bisa dilakukan warga
        $stepsLines = ['(c) Langkah yang Bisa Dilakukan Warga'];
        $stepsLines[] = '1. Catat secara lengkap waktu, lokasi kejadian, serta simpan bukti pendukung yang Anda miliki (seperti dokumen identitas, surat, foto, atau tanda terima).';
        if (! empty($sopChunks)) {
            $firstSop = $sopChunks[0];
            $stepsLines[] = sprintf(
                '2. Ikuti prosedur resmi sesuai %s (%s) pada %s: %s [Sitasi: %s (%s Tahun %d), %s — Sumber: %s].',
                $firstSop['document_name'],
                $firstSop['document_number'],
                $firstSop['article_label'],
                $firstSop['content'],
                $firstSop['document_name'],
                $firstSop['document_number'],
                $firstSop['document_year'],
                $firstSop['article_label'],
                $firstSop['source_url']
            );
        } else {
            $firstRule = $targetRuleChunks[0];
            $stepsLines[] = sprintf(
                '2. Ajukan permohonan atau pengaduan resmi dengan merujuk pada ketentuan %s (%s) %s [Sitasi: %s (%s Tahun %d), %s — Sumber: %s].',
                $firstRule['document_name'],
                $firstRule['document_number'],
                $firstRule['article_label'],
                $firstRule['document_name'],
                $firstRule['document_number'],
                $firstRule['document_year'],
                $firstRule['article_label'],
                $firstRule['source_url']
            );
        }
        $stepsLines[] = '3. Apabila kendala masih berlanjut atau membutuhkan penanganan lebih lanjut, sampaikan laporan resmi melalui kanal Pengaduan BHIMA atau hubungi petugas verifikator / Pos Bantuan Hukum (Posbakum) terdekat.';

        // (d) Rujukan SOP layanan bila ada
        $sopLines = ['(d) Rujukan SOP Layanan'];
        if (! empty($sopChunks)) {
            foreach ($sopChunks as $sop) {
                $sopLines[] = sprintf(
                    '- %s (%s Tahun %d), %s: %s [Sitasi: %s (%s Tahun %d), %s — Sumber: %s]',
                    $sop['document_name'],
                    $sop['document_number'],
                    $sop['document_year'],
                    $sop['article_label'],
                    $sop['content'],
                    $sop['document_name'],
                    $sop['document_number'],
                    $sop['document_year'],
                    $sop['article_label'],
                    $sop['source_url']
                );
            }
        } else {
            $sopLines[] = '- Belum ditemukan potongan khusus SOP Layanan yang terpisah pada konteks hasil pencarian ini; silakan merujuk pada prosedur di dalam peraturan di atas atau hubungi petugas layanan BHIMA.';
        }

        $disclaimer = (string) config('rag.disclaimer');

        return implode("\n\n", [
            $summarySection,
            implode("\n", $rulesLines),
            implode("\n", $stepsLines),
            implode("\n", $sopLines),
            $disclaimer,
        ]);
    }

    /**
     * Tambahkan penjelasan singkat istilah hukum agar mudah dipahami warga awam.
     */
    private function explainLegalTermsInPlainLanguage(string $content): string
    {
        $lower = mb_strtolower($content, 'UTF-8');
        $notes = [];

        if (str_contains($lower, 'bipartit')) {
            $notes[] = 'Artinya dalam bahasa sederhana: perundingan bipartit adalah musyawarah langsung antara pekerja dan pihak perusahaan untuk mencari jalan keluar bersama.';
        }
        if (str_contains($lower, 'litigasi') || str_contains($lower, 'nonlitigasi')) {
            $notes[] = 'Artinya dalam bahasa sederhana: litigasi adalah penyelesaian perkara di pengadilan, sedangkan nonlitigasi adalah pendampingan di luar pengadilan seperti konsultasi atau mediasi.';
        }
        if (str_contains($lower, 'gratifikasi') || str_contains($lower, 'pungutan')) {
            $notes[] = 'Artinya dalam bahasa sederhana: petugas dilarang meminta atau menerima uang/imbalan tambahan di luar ketentuan resmi.';
        }
        if (str_contains($lower, 'gawat darurat')) {
            $notes[] = 'Artinya dalam bahasa sederhana: pasien dalam kondisi darurat medis harus langsung ditolong terlebih dahulu tanpa boleh ditolak atau diminta uang muka.';
        }
        if (str_contains($lower, 'disposisi')) {
            $notes[] = 'Artinya dalam bahasa sederhana: disposisi adalah penerusan laporan warga dari petugas pemeriksa ke dinas teknis yang berwenang menangani.';
        }

        if (empty($notes)) {
            return 'Artinya bagi warga: ketentuan ini melindungi hak Anda agar memperoleh layanan dan perlindungan hukum sesuai standar resmi.';
        }

        return implode(' ', $notes);
    }

    /**
     * Pastikan tidak ada kalimat vonis/prediksi dan pastikan kalimat pengingat akhir selalu ada.
     */
    private function enforceOutputGuardrails(string $answer, bool $verdictRequested): string
    {
        $guardrailNotice = (string) config('rag.verdict_guardrail_notice');
        $disclaimer = (string) config('rag.disclaimer');

        $cleaned = preg_replace(
            self::PROHIBITED_OUTPUT_PATTERNS,
            'kami tidak dapat memprediksi atau menjamin menang/kalah perkara',
            $answer
        ) ?? $answer;

        if ($verdictRequested && ! str_contains($cleaned, 'tidak berwenang memberikan vonis') && ! str_contains($cleaned, 'tidak dapat memprediksi')) {
            $cleaned = $guardrailNotice."\n\n".$cleaned;
        }

        if (! str_contains($cleaned, 'bukan merupakan nasihat hukum resmi') && ! str_contains($cleaned, 'bukan nasihat hukum resmi')) {
            $cleaned = rtrim($cleaned)."\n\n".$disclaimer;
        }

        return $cleaned;
    }

    /**
     * @param  array<string, mixed>  $chunk
     * @return array<string, mixed>
     */
    private function formatCitationFromChunk(array $chunk): array
    {
        return [
            'chunk_id' => (int) $chunk['chunk_id'],
            'document_key' => (string) $chunk['document_key'],
            'document_name' => (string) $chunk['document_name'],
            'document_type' => (string) $chunk['document_type'],
            'document_number' => (string) $chunk['document_number'],
            'document_year' => (int) $chunk['document_year'],
            'article_label' => (string) $chunk['article_label'],
            'source_url' => (string) $chunk['source_url'],
            'category' => (string) $chunk['category'],
            'topic' => (string) ($chunk['topic'] ?? 'Umum'),
            'score' => round((float) $chunk['score'], 4),
            'excerpt' => (string) $chunk['content'],
        ];
    }
}
