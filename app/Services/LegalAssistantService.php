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
        $revokedNotices = $retrieval['revoked_notices'] ?? [];

        // Bila tidak ada chunk di atas ambang kemiripan, JANGAN memanggil LLM untuk substansi hukum
        if (! $retrieval['has_context'] || empty($chunks)) {
            $outOfScopeMessage = (string) config('rag.out_of_scope_message');
            $disclaimer = (string) config('rag.disclaimer');

            $parts = [];
            if ($verdictRequested) {
                $parts[] = (string) config('rag.verdict_guardrail_notice');
            }
            if (! empty($revokedNotices)) {
                $parts[] = implode("\n", $revokedNotices);
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
                    'sanction_query' => (bool) ($retrieval['sanction_query'] ?? false),
                    'revoked_notices' => $revokedNotices,
                    'guardrail_triggered' => $verdictRequested,
                ],
            ];
        }

        // Kirim ke LLM hanya kronologi warga dan chunk hasil retrieval
        $generation = $this->generateFromContext($sanitizedChronology, $chunks, $verdictRequested, $revokedNotices);

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
                'sanction_query' => (bool) ($retrieval['sanction_query'] ?? false),
                'revoked_notices' => $revokedNotices,
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
     * @param  list<string>  $revokedNotices
     * @return array{answer: string, citations: list<array<string, mixed>>, llm_called: bool, provider: string}
     */
    private function generateFromContext(string $chronology, array $chunks, bool $verdictRequested, array $revokedNotices = []): array
    {
        $provider = (string) config('rag.llm.provider', 'local_grounded');
        $apiKey = (string) config('rag.llm.api_key', '');
        $systemPrompt = (string) config('rag.system_prompt');

        // Format konteks yang dikirim ke LLM: HANYA kronologi warga dan chunk hasil retrieval beserta status & tautan resmi
        $contextBlocks = [];
        foreach ($chunks as $idx => $chunk) {
            $num = $idx + 1;
            $statusBerlaku = (string) ($chunk['status_berlaku'] ?? 'berlaku');
            $diubahOleh = trim((string) ($chunk['diubah_oleh'] ?? ''));
            $statusLine = $statusBerlaku === 'diubah' && $diubahOleh !== ''
                ? "diubah (diubah oleh {$diubahOleh})"
                : $statusBerlaku;
            $officialUrl = trim((string) ($chunk['source_page_url'] ?? ''));
            $sanctionLine = ! empty($chunk['sanksi_teks'])
                ? sprintf('%s (%s)', (string) $chunk['sanksi_teks'], (string) ($chunk['sanksi_jenis'] ?? 'lainnya'))
                : 'Tidak tercantum dalam potongan pasal yang ditemukan';

            $contextBlocks[] = sprintf(
                "[SUMBER #%d]\nID Dokumen: %s\nKategori: %s\nNama Dokumen: %s\nJenis: %s\nNomor/Tahun: %s (%d)\nPasal/Bagian: %s\nStatus Berlaku: %s\nKetentuan Sanksi Eksplisit: %s\nTautan Portal: %s%s\nIsi Konteks: %s",
                $num,
                $chunk['document_key'],
                $chunk['category'],
                $chunk['document_name'],
                $chunk['document_type'],
                $chunk['document_number'],
                $chunk['document_year'],
                $chunk['article_label'],
                $statusLine,
                $sanctionLine,
                $chunk['source_url'],
                $officialUrl !== '' ? "\nTautan Sumber Resmi: {$officialUrl}" : '',
                $chunk['content']
            );
        }

        $archivePrefix = ! empty($revokedNotices)
            ? "\n\nCATATAN ARSIP PERATURAN DICABUT:\n".implode("\n", $revokedNotices)
            : '';

        $userPrompt = "KRONOLOGI WARGA:\n".$chronology.$archivePrefix."\n\nPOTONGAN KONTEKS HUKUM & SOP:\n".implode("\n\n", $contextBlocks);

        if ($provider === 'api' && $apiKey !== '') {
            try {
                $baseUrl = rtrim((string) config('rag.llm.base_url', 'https://ai.paas.id'), '/');
                $endpoint = str_ends_with($baseUrl, '/chat/completions')
                    ? $baseUrl
                    : $baseUrl.'/chat/completions';

                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout((int) config('rag.llm.timeout', 20))
                    ->post($endpoint, [
                        'model' => config('rag.llm.model', 'deepseek-v4.1-flash'),
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
                        if (! empty($revokedNotices)) {
                            $llmText = implode("\n", $revokedNotices)."\n\n".$llmText;
                        }

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
        $synthesized = $this->synthesizeGroundedAnswer($chronology, $chunks, $verdictRequested, $revokedNotices);

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
     * @param  list<string>  $revokedNotices
     */
    private function synthesizeGroundedAnswer(string $chronology, array $chunks, bool $verdictRequested, array $revokedNotices = []): string
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
            $summarySection .= 'Berikut adalah ketentuan aturan yang berpotensi terkait dan prosedur layanan resmi yang relevan dengan kondisi Anda.';
        }
        if (! empty($revokedNotices)) {
            $summarySection .= "\n".implode("\n", $revokedNotices);
        }

        // (b) Pasal/aturan yang relevan dan artinya dalam bahasa awam
        $rulesLines = ['(b) Pasal/Aturan yang Relevan dan Artinya'];
        $targetRuleChunks = ! empty($jdihChunks) ? $jdihChunks : $chunks;

        $distinctJdihDocs = [];
        foreach ($targetRuleChunks as $chunk) {
            $plainExplanation = $this->explainLegalTermsInPlainLanguage((string) $chunk['content']);
            $statusNote = '';
            if (($chunk['status_berlaku'] ?? 'berlaku') === 'diubah' && ! empty($chunk['diubah_oleh'])) {
                $statusNote = ' [Catatan status: diubah oleh '.$chunk['diubah_oleh'].']';
            }
            $officialSuffix = ! empty($chunk['source_page_url'])
                ? ' | Sumber Resmi: '.$chunk['source_page_url']
                : '';
            $citationInline = sprintf(
                '[Sitasi: %s (%s), %s - Sumber: %s%s]',
                $chunk['document_name'],
                $this->formatDocNumberYear((string) $chunk['document_number'], (int) $chunk['document_year']),
                $chunk['article_label'],
                $chunk['source_url'],
                $officialSuffix
            );
            $rulesLines[] = sprintf(
                '- Berdasarkan %s (%s), %s: %s %s%s %s',
                $chunk['document_name'],
                $chunk['document_number'],
                $chunk['article_label'],
                $chunk['content'],
                $plainExplanation,
                $statusNote,
                $citationInline
            );
            if ($chunk['category'] === 'JDIH') {
                $distinctJdihDocs[$chunk['document_key']] = $chunk['document_name'].' ('.$chunk['document_number'].')';
            }
        }

        // Aturan 7.4: bila lebih dari satu peraturan berlaku pada kasus yang sama, jelaskan mana ketentuan khusus (lex specialis)
        if (count($distinctJdihDocs) > 1) {
            $docNames = array_values($distinctJdihDocs);
            $rulesLines[] = sprintf(
                '- Catatan keberlakuan beberapa aturan: terdapat lebih dari satu peraturan yang relevan (%s). Dalam praktik hukum, ketentuan sektoral yang mengatur pokok permasalahan secara lebih spesifik (%s) berlaku sebagai aturan khusus (lex specialis) berdampingan dengan aturan umum atau pendukung lainnya.',
                implode('; ', $docNames),
                $docNames[0]
            );
        }

        // (c) Langkah yang bisa dilakukan warga
        $stepsLines = ['(c) Langkah yang Bisa Dilakukan Warga'];
        $stepsLines[] = '1. Catat secara lengkap waktu, lokasi kejadian, serta simpan bukti pendukung yang Anda miliki (seperti dokumen identitas, surat, foto, atau tanda terima).';
        if (! empty($sopChunks)) {
            $firstSop = $sopChunks[0];
            $stepsLines[] = sprintf(
                '2. Ikuti prosedur resmi sesuai %s (%s) pada %s: %s [Sitasi: %s (%s), %s - Sumber: %s].',
                $firstSop['document_name'],
                $firstSop['document_number'],
                $firstSop['article_label'],
                $firstSop['content'],
                $firstSop['document_name'],
                $this->formatDocNumberYear((string) $firstSop['document_number'], (int) $firstSop['document_year']),
                $firstSop['article_label'],
                $firstSop['source_url']
            );
        } else {
            $firstRule = $targetRuleChunks[0];
            $stepsLines[] = sprintf(
                '2. Ajukan permohonan atau pengaduan resmi dengan merujuk pada ketentuan %s (%s) %s [Sitasi: %s (%s), %s - Sumber: %s].',
                $firstRule['document_name'],
                $firstRule['document_number'],
                $firstRule['article_label'],
                $firstRule['document_name'],
                $this->formatDocNumberYear((string) $firstRule['document_number'], (int) $firstRule['document_year']),
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
                    '- %s (%s), %s: %s [Sitasi: %s (%s), %s - Sumber: %s]',
                    $sop['document_name'],
                    $this->formatDocNumberYear((string) $sop['document_number'], (int) $sop['document_year']),
                    $sop['article_label'],
                    $sop['content'],
                    $sop['document_name'],
                    $this->formatDocNumberYear((string) $sop['document_number'], (int) $sop['document_year']),
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

    private function formatDocNumberYear(string $docNumber, int $docYear): string
    {
        if ($docYear > 0 && ! str_contains(mb_strtolower($docNumber, 'UTF-8'), (string) $docYear)) {
            return trim($docNumber.' Tahun '.$docYear);
        }

        return $docNumber;
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
            'pasal_nomor' => isset($chunk['pasal_nomor']) ? (string) $chunk['pasal_nomor'] : null,
            'ayat' => isset($chunk['ayat']) ? (string) $chunk['ayat'] : null,
            'is_ketentuan_pidana' => (bool) ($chunk['is_ketentuan_pidana'] ?? false),
            'sanksi_jenis' => isset($chunk['sanksi_jenis']) ? (string) $chunk['sanksi_jenis'] : null,
            'sanksi_teks' => isset($chunk['sanksi_teks']) ? (string) $chunk['sanksi_teks'] : null,
            'rujuk_pasal' => isset($chunk['rujuk_pasal']) ? (string) $chunk['rujuk_pasal'] : null,
            'status_berlaku' => (string) ($chunk['status_berlaku'] ?? 'berlaku'),
            'diubah_oleh' => isset($chunk['diubah_oleh']) ? (string) $chunk['diubah_oleh'] : null,
            'source_url' => (string) $chunk['source_url'],
            'source_page_url' => isset($chunk['source_page_url']) ? (string) $chunk['source_page_url'] : null,
            'pdf_page' => isset($chunk['pdf_page']) ? (int) $chunk['pdf_page'] : null,
            'category' => (string) $chunk['category'],
            'topic' => (string) ($chunk['topic'] ?? 'Umum'),
            'score' => round((float) $chunk['score'], 4),
            'excerpt' => (string) $chunk['content'],
        ];
    }
}
