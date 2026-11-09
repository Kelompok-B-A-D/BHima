<?php

namespace App\Services;

use App\Contracts\AnalysisProvider;
use App\Exceptions\AnalysisProviderException;
use App\Services\Analysis\DeepSeekProvider;
use App\Services\Analysis\MockProvider;
use Illuminate\Support\Facades\Log;

class LegalAnalysisService
{
    private const ALLOWED_SANCTION_TYPES = ['pidana', 'administratif', 'perdata', 'lainnya'];

    /**
     * Pola kalimat terlarang (prediksi vonis / peluang menang atau kalah).
     *
     * @var list<string>
     */
    private const PROHIBITED_VERDICT_PATTERNS = [
        '/anda\s+pasti\s+menang/iu',
        '/dijamin\s+menang/iu',
        '/dipastikan\s+menang/iu',
        '/peluang\s+menang\s+\d+\s*%/iu',
        '/kemungkinan\s+menang\s+\d+\s*%/iu',
        '/anda\s+pasti\s+kalah/iu',
        '/pihak\s+lawan\s+pasti\s+kalah/iu',
        '/pasti\s+divonis\s+bersalah/iu',
        '/pasti\s+dipenjara/iu',
        '/kami\s+menjamin\s+anda\s+menang/iu',
    ];

    /**
     * Kata kunci pertanyaan yang meminta prediksi menang/kalah atau vonis.
     *
     * @var list<string>
     */
    private const VERDICT_QUERY_KEYWORDS = [
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
    ];

    /**
     * Jalankan analisis pasal, ancaman sanksi, dan rekomendasi eskalasi dari kronologi warga
     * serta chunk hasil retrieval PBI 1.
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array{
     *     rujukan: list<array{dokumen: string, nomor_tahun: string, tautan: string}>,
     *     pasal_relevan: list<array{pasal: string, dokumen: string, penjelasan_awam: string, alasan_terkait_kronologi: string}>,
     *     ancaman_sanksi: list<array{pasal: string, jenis: string, bunyi_sanksi_ringkas: string, dokumen: string}>,
     *     perlu_eskalasi: bool,
     *     catatan: string,
     *     meta: array<string, mixed>
     * }
     */
    public function analyze(string $chronology, array $chunks, ?AnalysisProvider $customProvider = null): array
    {
        $normalizedChunks = $this->normalizeChunks($chunks);
        $provider = $customProvider ?? $this->resolveProvider();
        $verdictRequested = $this->detectVerdictQuestion($chronology);

        if (empty($normalizedChunks)) {
            $emptyNote = 'Konteks pasal JDIH atau SOP layanan yang ditemukan belum cukup relevan dengan kronologi yang disampaikan. Silakan gunakan tombol Ajukan Pengaduan agar permasalahan Anda ditinjau langsung oleh petugas verifikator BHIMA.';
            if ($verdictRequested) {
                $emptyNote = (string) config('legal_analysis.verdict_guardrail_note').' '.$emptyNote;
            }

            return [
                'rujukan' => [],
                'pasal_relevan' => [],
                'ancaman_sanksi' => [],
                'perlu_eskalasi' => true,
                'catatan' => $emptyNote,
                'meta' => [
                    'provider' => $provider->name(),
                    'status' => 'out_of_scope',
                    'error' => null,
                    'removed_entries_count' => 0,
                    'verdict_guardrail_triggered' => $verdictRequested,
                    'sanction_label' => (string) config('legal_analysis.sanction_label', 'berdasarkan bunyi pasal, bukan prediksi putusan'),
                    'disclaimer' => (string) config('legal_analysis.disclaimer'),
                ],
            ];
        }

        $maskedChronology = DeepSeekProvider::maskPersonalIdentifiers($chronology);
        $rawResult = null;
        $maxRetries = 1;

        try {
            for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
                $candidate = $provider->analyze($maskedChronology, $normalizedChunks);
                if (self::isValidSchema($candidate)) {
                    $rawResult = $candidate;
                    break;
                }

                Log::warning('LegalAnalysisService: Keluaran provider tidak memenuhi skema tetap.', [
                    'provider' => $provider->name(),
                    'attempt' => $attempt + 1,
                ]);
            }

            if ($rawResult === null) {
                throw new AnalysisProviderException(
                    'Maaf, hasil analisis pasal belum dapat ditampilkan karena respons layanan tidak sesuai format standar. Silakan coba lagi atau langsung ajukan pengaduan.',
                    'invalid_json'
                );
            }
        } catch (AnalysisProviderException $e) {
            return $this->buildGracefulErrorResponse($provider->name(), $e->getMessage(), $e->errorCode, $verdictRequested);
        } catch (\Throwable $e) {
            Log::error('LegalAnalysisService: Kegagalan saat memproses analisis pasal.', [
                'provider' => $provider->name(),
                'error' => $e->getMessage(),
            ]);

            return $this->buildGracefulErrorResponse(
                $provider->name(),
                'Maaf, layanan analisis pasal sedang mengalami gangguan teknis sementara. Anda tetap dapat mengajukan pengaduan langsung melalui tombol di bawah.',
                'unexpected_error',
                $verdictRequested
            );
        }

        $validated = $this->validateAndFilterOutput($rawResult, $normalizedChunks, $verdictRequested);

        return [
            'rujukan' => $validated['rujukan'],
            'pasal_relevan' => $validated['pasal_relevan'],
            'ancaman_sanksi' => $validated['ancaman_sanksi'],
            'perlu_eskalasi' => $validated['perlu_eskalasi'],
            'catatan' => $validated['catatan'],
            'meta' => [
                'provider' => $provider->name(),
                'status' => 'ok',
                'error' => null,
                'removed_entries_count' => $validated['removed_entries_count'],
                'verdict_guardrail_triggered' => $verdictRequested,
                'sanction_label' => (string) config('legal_analysis.sanction_label', 'berdasarkan bunyi pasal, bukan prediksi putusan'),
                'disclaimer' => (string) config('legal_analysis.disclaimer'),
            ],
        ];
    }

    /**
     * Pilih implementasi AnalysisProvider berdasarkan konfigurasi LLM_PROVIDER (mock | deepseek).
     */
    public function resolveProvider(): AnalysisProvider
    {
        $configured = strtolower(trim((string) config('legal_analysis.provider', 'mock')));

        if ($configured === 'deepseek') {
            return app(DeepSeekProvider::class);
        }

        return app(MockProvider::class);
    }

    /**
     * Validasi apakah array keluaran memenuhi skema JSON tetap PBI 2.
     *
     * @param  array<string, mixed>  $data
     */
    public static function isValidSchema(array $data): bool
    {
        $requiredTopKeys = ['rujukan', 'pasal_relevan', 'ancaman_sanksi', 'perlu_eskalasi', 'catatan'];
        foreach ($requiredTopKeys as $key) {
            if (! array_key_exists($key, $data)) {
                return false;
            }
        }

        if (! is_array($data['rujukan']) || ! is_array($data['pasal_relevan']) || ! is_array($data['ancaman_sanksi'])) {
            return false;
        }

        if (! is_bool($data['perlu_eskalasi']) || ! is_string($data['catatan'])) {
            return false;
        }

        foreach ($data['rujukan'] as $item) {
            if (! is_array($item)
                || ! isset($item['dokumen'], $item['nomor_tahun'], $item['tautan'])
                || ! is_string($item['dokumen'])
                || ! is_string($item['nomor_tahun'])
                || ! is_string($item['tautan'])
            ) {
                return false;
            }
        }

        foreach ($data['pasal_relevan'] as $item) {
            if (! is_array($item)
                || ! isset($item['pasal'], $item['dokumen'], $item['penjelasan_awam'], $item['alasan_terkait_kronologi'])
                || ! is_string($item['pasal'])
                || ! is_string($item['dokumen'])
                || ! is_string($item['penjelasan_awam'])
                || ! is_string($item['alasan_terkait_kronologi'])
            ) {
                return false;
            }
        }

        foreach ($data['ancaman_sanksi'] as $item) {
            if (! is_array($item)
                || ! isset($item['pasal'], $item['jenis'], $item['bunyi_sanksi_ringkas'], $item['dokumen'])
                || ! is_string($item['pasal'])
                || ! is_string($item['jenis'])
                || ! is_string($item['bunyi_sanksi_ringkas'])
                || ! is_string($item['dokumen'])
                || ! in_array(strtolower(trim($item['jenis'])), self::ALLOWED_SANCTION_TYPES, true)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validasi pasca model (di kode PHP):
     * - Setiap pasal dan dokumen pada keluaran WAJIB cocok dengan chunk yang dikirim.
     * - Buang entri rujukan, pasal_relevan, atau ancaman_sanksi yang tidak ada di konteks chunk.
     * - Sanksi yang muncul harus berasal dari teks chunk terkait; jika chunk tidak memuat sanksi
     *   atau model mengarang sanksi di luar teks chunk, ganti dengan teks "Tidak tercantum dalam potongan pasal yang ditemukan".
     * - Hapus segala bentuk prediksi vonis atau peluang menang/kalah.
     *
     * @param  array<string, mixed>  $rawOutput
     * @param  list<array<string, mixed>>  $chunks
     * @return array{
     *     rujukan: list<array{dokumen: string, nomor_tahun: string, tautan: string}>,
     *     pasal_relevan: list<array{pasal: string, dokumen: string, penjelasan_awam: string, alasan_terkait_kronologi: string}>,
     *     ancaman_sanksi: list<array{pasal: string, jenis: string, bunyi_sanksi_ringkas: string, dokumen: string}>,
     *     perlu_eskalasi: bool,
     *     catatan: string,
     *     removed_entries_count: int
     * }
     */
    public function validateAndFilterOutput(array $rawOutput, array $chunks, bool $verdictRequested = false): array
    {
        $noSanctionText = (string) config(
            'legal_analysis.no_sanction_text',
            'Tidak tercantum dalam potongan pasal yang ditemukan'
        );

        $removedCount = 0;
        $allowedArticleNumbers = [];

        foreach ($chunks as $chunk) {
            if (preg_match('/Pasal\s+(\d+[A-Z]?)/iu', (string) ($chunk['article_label'] ?? ''), $m)) {
                $allowedArticleNumbers[] = mb_strtolower($m[1], 'UTF-8');
            }
        }

        // 1. Validasi Rujukan Dokumen terhadap $chunks
        $validRujukan = [];
        $seenRujukanKeys = [];
        foreach (($rawOutput['rujukan'] ?? []) as $rujukanItem) {
            $matchedChunk = $this->findChunkByDocument((string) ($rujukanItem['dokumen'] ?? ''), (string) ($rujukanItem['nomor_tahun'] ?? ''), $chunks);
            if ($matchedChunk === null) {
                $removedCount++;

                continue;
            }

            $docName = (string) $matchedChunk['document_name'];
            $docNumber = (string) $matchedChunk['document_number'];
            $docYear = (string) $matchedChunk['document_year'];
            $nomorTahun = ($docYear !== '' && ! str_contains(mb_strtolower($docNumber, 'UTF-8'), mb_strtolower($docYear, 'UTF-8')))
                ? trim($docNumber.' Tahun '.$docYear)
                : $docNumber;
            $dedupKey = mb_strtolower($docName.'|'.$nomorTahun, 'UTF-8');

            if (! isset($seenRujukanKeys[$dedupKey])) {
                $seenRujukanKeys[$dedupKey] = true;
                $validRujukan[] = [
                    'dokumen' => $docName,
                    'nomor_tahun' => $nomorTahun,
                    'tautan' => (string) $matchedChunk['source_url'],
                ];
            }
        }

        // 2. Validasi Pasal Relevan terhadap $chunks
        $validPasalRelevan = [];
        $seenPasalKeys = [];
        foreach (($rawOutput['pasal_relevan'] ?? []) as $pasalItem) {
            $matchedChunk = $this->findChunkByArticleAndDocument(
                (string) ($pasalItem['pasal'] ?? ''),
                (string) ($pasalItem['dokumen'] ?? ''),
                $chunks
            );

            if ($matchedChunk === null) {
                $removedCount++;

                continue;
            }

            $canonicalPasal = (string) $matchedChunk['article_label'];
            $canonicalDoc = (string) $matchedChunk['document_name'];
            $dedupKey = mb_strtolower($canonicalDoc.'|'.$canonicalPasal, 'UTF-8');

            if (isset($seenPasalKeys[$dedupKey])) {
                continue;
            }
            $seenPasalKeys[$dedupKey] = true;

            $penjelasan = $this->scrubUnverifiedArticlesAndVerdicts(
                (string) ($pasalItem['penjelasan_awam'] ?? ''),
                $allowedArticleNumbers,
                $removedCount
            );
            $alasan = $this->scrubUnverifiedArticlesAndVerdicts(
                (string) ($pasalItem['alasan_terkait_kronologi'] ?? ''),
                $allowedArticleNumbers,
                $removedCount
            );

            // Pastikan frasa "pasal yang dilanggar" diganti menjadi "pasal yang berpotensi terkait"
            $penjelasan = preg_replace('/\bpasal\s+yang\s+dilanggar\b/iu', 'pasal yang berpotensi terkait', $penjelasan) ?? $penjelasan;
            $alasan = preg_replace('/\bpasal\s+yang\s+dilanggar\b/iu', 'pasal yang berpotensi terkait', $alasan) ?? $alasan;

            $validPasalRelevan[] = [
                'pasal' => $canonicalPasal,
                'dokumen' => $canonicalDoc,
                'penjelasan_awam' => $penjelasan,
                'alasan_terkait_kronologi' => $alasan,
            ];
        }

        // 3. Validasi Ancaman Sanksi terhadap teks chunk terkait
        $validAncamanSanksi = [];
        $seenSanksiKeys = [];
        foreach (($rawOutput['ancaman_sanksi'] ?? []) as $sanksiItem) {
            $matchedChunk = $this->findChunkByArticleAndDocument(
                (string) ($sanksiItem['pasal'] ?? ''),
                (string) ($sanksiItem['dokumen'] ?? ''),
                $chunks
            );

            if ($matchedChunk === null) {
                $removedCount++;

                continue;
            }

            $canonicalPasal = (string) $matchedChunk['article_label'];
            $canonicalDoc = (string) $matchedChunk['document_name'];
            $dedupKey = mb_strtolower($canonicalDoc.'|'.$canonicalPasal, 'UTF-8');

            if (isset($seenSanksiKeys[$dedupKey])) {
                continue;
            }
            $seenSanksiKeys[$dedupKey] = true;

            $chunkContent = (string) ($matchedChunk['content'] ?? '');
            $groundTruthSanction = MockProvider::extractSanctionFromChunkContent($chunkContent, $noSanctionText);
            $candidateSanctionText = trim((string) ($sanksiItem['bunyi_sanksi_ringkas'] ?? ''));
            $candidateJenis = strtolower(trim((string) ($sanksiItem['jenis'] ?? 'lainnya')));
            if (! in_array($candidateJenis, self::ALLOWED_SANCTION_TYPES, true)) {
                $candidateJenis = 'lainnya';
            }

            // Jika di dalam teks chunk tidak ada sanksi eksplisit, wajib gunakan $noSanctionText
            if ($groundTruthSanction['bunyi_sanksi_ringkas'] === $noSanctionText) {
                if ($candidateSanctionText !== '' && strcasecmp($candidateSanctionText, $noSanctionText) !== 0) {
                    $removedCount++;
                }
                $verifiedSanctionText = $noSanctionText;
                $verifiedJenis = 'lainnya';
            } elseif (
                $candidateSanctionText === ''
                || strcasecmp($candidateSanctionText, $noSanctionText) === 0
                || ! $this->isSanctionGroundedInChunk($candidateSanctionText, $chunkContent)
            ) {
                if ($candidateSanctionText !== '' && strcasecmp($candidateSanctionText, $noSanctionText) !== 0) {
                    $removedCount++;
                }
                $verifiedSanctionText = $groundTruthSanction['bunyi_sanksi_ringkas'];
                $verifiedJenis = $groundTruthSanction['jenis'];
            } else {
                $verifiedSanctionText = $this->scrubUnverifiedArticlesAndVerdicts($candidateSanctionText, $allowedArticleNumbers, $removedCount);
                $verifiedJenis = $candidateJenis === 'lainnya' ? $groundTruthSanction['jenis'] : $candidateJenis;
            }

            $validAncamanSanksi[] = [
                'pasal' => $canonicalPasal,
                'jenis' => $verifiedJenis,
                'bunyi_sanksi_ringkas' => $verifiedSanctionText,
                'dokumen' => $canonicalDoc,
            ];
        }

        // Pastikan rujukan mencakup seluruh dokumen yang muncul di pasal_relevan / ancaman_sanksi
        foreach (array_merge($validPasalRelevan, $validAncamanSanksi) as $entry) {
            $matchedChunk = $this->findChunkByDocument($entry['dokumen'], '', $chunks);
            if ($matchedChunk !== null) {
                $docName = (string) $matchedChunk['document_name'];
                $docNumber = (string) $matchedChunk['document_number'];
                $docYear = (string) $matchedChunk['document_year'];
                $nomorTahun = ($docYear !== '' && ! str_contains(mb_strtolower($docNumber, 'UTF-8'), mb_strtolower($docYear, 'UTF-8')))
                    ? trim($docNumber.' Tahun '.$docYear)
                    : $docNumber;
                $dedupKey = mb_strtolower($docName.'|'.$nomorTahun, 'UTF-8');

                if (! isset($seenRujukanKeys[$dedupKey])) {
                    $seenRujukanKeys[$dedupKey] = true;
                    $validRujukan[] = [
                        'dokumen' => $docName,
                        'nomor_tahun' => $nomorTahun,
                        'tautan' => (string) $matchedChunk['source_url'],
                    ];
                }
            }
        }

        $catatan = $this->scrubUnverifiedArticlesAndVerdicts(
            trim((string) ($rawOutput['catatan'] ?? '')),
            $allowedArticleNumbers,
            $removedCount
        );

        if ($verdictRequested) {
            $guardrailNote = (string) config('legal_analysis.verdict_guardrail_note');
            if (! str_contains(mb_strtolower($catatan, 'UTF-8'), 'tidak berwenang memberikan vonis')) {
                $catatan = trim($guardrailNote.' '.$catatan);
            }
        }

        if ($catatan === '') {
            $catatan = (string) config('legal_analysis.disclaimer');
        }

        $perluEskalasi = (bool) ($rawOutput['perlu_eskalasi'] ?? true);
        if (empty($validPasalRelevan)) {
            $perluEskalasi = true;
        }

        return [
            'rujukan' => array_values($validRujukan),
            'pasal_relevan' => array_values($validPasalRelevan),
            'ancaman_sanksi' => array_values($validAncamanSanksi),
            'perlu_eskalasi' => $perluEskalasi,
            'catatan' => $catatan,
            'removed_entries_count' => $removedCount,
        ];
    }

    public function detectVerdictQuestion(string $chronology): bool
    {
        $lower = mb_strtolower($chronology, 'UTF-8');
        foreach (self::VERDICT_QUERY_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return (bool) preg_match('/\b(apakah|bisakah|mungkinkah)\b.*\b(pasti\s+menang|menang\s+sidang|menang\s+perkara|divonis\s+bebas)\b/iu', $lower);
    }

    /**
     * Normalisasi format chunk (mendukung struktur chunk dari LegalRetrievalService maupun struktur citations).
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return list<array<string, mixed>>
     */
    private function normalizeChunks(array $chunks): array
    {
        $normalized = [];
        foreach ($chunks as $chunk) {
            if (! is_array($chunk)) {
                continue;
            }
            $content = (string) ($chunk['content'] ?? $chunk['excerpt'] ?? '');
            $docName = (string) ($chunk['document_name'] ?? '');
            $articleLabel = (string) ($chunk['article_label'] ?? '');
            if ($content === '' || $docName === '' || $articleLabel === '') {
                continue;
            }

            $normalized[] = [
                'chunk_id' => (int) ($chunk['chunk_id'] ?? 0),
                'document_key' => (string) ($chunk['document_key'] ?? ''),
                'document_name' => $docName,
                'document_type' => (string) ($chunk['document_type'] ?? 'Dokumen Rujukan'),
                'document_number' => (string) ($chunk['document_number'] ?? '-'),
                'document_year' => (int) ($chunk['document_year'] ?? 0),
                'article_label' => $articleLabel,
                'source_url' => (string) ($chunk['source_url'] ?? ''),
                'category' => (string) ($chunk['category'] ?? 'JDIH'),
                'topic' => (string) ($chunk['topic'] ?? 'Umum'),
                'content' => $content,
                'score' => (float) ($chunk['score'] ?? 0.0),
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     * @return array<string, mixed>|null
     */
    private function findChunkByDocument(string $docCandidate, string $nomorTahunCandidate, array $chunks): ?array
    {
        $docNorm = $this->normalizeComparableText($docCandidate);
        $numNorm = $this->normalizeComparableText($nomorTahunCandidate);

        foreach ($chunks as $chunk) {
            $cName = $this->normalizeComparableText((string) $chunk['document_name']);
            $cNum = $this->normalizeComparableText((string) $chunk['document_number'].' '.(string) $chunk['document_year']);
            $cKey = $this->normalizeComparableText((string) $chunk['document_key']);

            if ($docNorm !== '' && ($cName === $docNorm || str_contains($cName, $docNorm) || str_contains($docNorm, $cName) || $cKey === $docNorm)) {
                return $chunk;
            }
            if ($numNorm !== '' && ($cNum === $numNorm || str_contains($cNum, $numNorm))) {
                return $chunk;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     * @return array<string, mixed>|null
     */
    private function findChunkByArticleAndDocument(string $pasalCandidate, string $docCandidate, array $chunks): ?array
    {
        $pasalNorm = $this->normalizeComparableText($pasalCandidate);
        $docNorm = $this->normalizeComparableText($docCandidate);

        if ($pasalNorm === '' || $docNorm === '') {
            return null;
        }

        foreach ($chunks as $chunk) {
            $cPasal = $this->normalizeComparableText((string) $chunk['article_label']);
            $cDoc = $this->normalizeComparableText((string) $chunk['document_name']);
            $cNum = $this->normalizeComparableText((string) $chunk['document_number']);
            $cKey = $this->normalizeComparableText((string) $chunk['document_key']);

            $pasalMatches = ($cPasal === $pasalNorm);
            $docMatches = ($cDoc === $docNorm || str_contains($cDoc, $docNorm) || str_contains($docNorm, $cDoc) || $cNum === $docNorm || $cKey === $docNorm);

            if ($pasalMatches && $docMatches) {
                return $chunk;
            }
        }

        return null;
    }

    /**
     * Pastikan bunyi sanksi dari model benar-benar berasal dari teks chunk terkait
     * (seluruh angka/nominal di bunyi sanksi harus ada di dalam teks chunk).
     */
    private function isSanctionGroundedInChunk(string $candidateSanction, string $chunkContent): bool
    {
        $chunkLower = mb_strtolower(preg_replace('/\s+/u', ' ', $chunkContent) ?? '', 'UTF-8');
        $candLower = mb_strtolower(preg_replace('/\s+/u', ' ', $candidateSanction) ?? '', 'UTF-8');

        // Jika langsung merupakan substring dari teks chunk
        if (str_contains($chunkLower, $candLower)) {
            return true;
        }

        // Semua angka yang disebut pada sanksi kandidat WAJIB ada persis di dalam teks chunk
        preg_match_all('/\d+(?:[.,]\d+)*/u', $candLower, $candNumbers);
        foreach ($candNumbers[0] as $num) {
            if (! str_contains($chunkLower, $num)) {
                return false;
            }
        }

        // Minimal kata kunci sanksi utama di kandidat juga harus ada di dalam teks chunk
        $sanctionKeywords = ['pidana', 'penjara', 'denda', 'disiplin', 'sanksi', 'ganti rugi', 'pemberhentian'];
        $matchedKeyword = false;
        foreach ($sanctionKeywords as $kw) {
            if (str_contains($candLower, $kw)) {
                if (! str_contains($chunkLower, $kw)) {
                    return false;
                }
                $matchedKeyword = true;
            }
        }

        return $matchedKeyword;
    }

    /**
     * Bersihkan klaim vonis/menang-kalah dan penyebutan Pasal di luar konteks.
     *
     * @param  list<string>  $allowedArticleNumbers
     */
    private function scrubUnverifiedArticlesAndVerdicts(string $text, array $allowedArticleNumbers, int &$removedCount): string
    {
        $cleaned = preg_replace(
            self::PROHIBITED_VERDICT_PATTERNS,
            'kami tidak dapat memprediksi vonis atau peluang menang/kalah perkara',
            $text
        ) ?? $text;

        $cleaned = preg_replace_callback(
            '/\bPasal\s+(\d+[A-Z]?)\b/iu',
            function (array $matches) use ($allowedArticleNumbers, &$removedCount) {
                $num = mb_strtolower($matches[1], 'UTF-8');
                if (in_array($num, $allowedArticleNumbers, true)) {
                    return $matches[0];
                }
                $removedCount++;

                return '[Pasal di luar konteks dihapus]';
            },
            $cleaned
        ) ?? $cleaned;

        return trim($cleaned);
    }

    private function normalizeComparableText(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $clean = preg_replace('/[^a-z0-9]+/u', ' ', $lower) ?? $lower;

        return trim(preg_replace('/\s+/u', ' ', $clean) ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGracefulErrorResponse(string $providerName, string $userMessage, string $errorCode, bool $verdictRequested): array
    {
        $note = $userMessage;
        if ($verdictRequested) {
            $note = (string) config('legal_analysis.verdict_guardrail_note').' '.$note;
        }

        return [
            'rujukan' => [],
            'pasal_relevan' => [],
            'ancaman_sanksi' => [],
            'perlu_eskalasi' => true,
            'catatan' => $note,
            'meta' => [
                'provider' => $providerName,
                'status' => 'error',
                'error' => $errorCode,
                'error_message' => $userMessage,
                'removed_entries_count' => 0,
                'verdict_guardrail_triggered' => $verdictRequested,
                'sanction_label' => (string) config('legal_analysis.sanction_label', 'berdasarkan bunyi pasal, bukan prediksi putusan'),
                'disclaimer' => (string) config('legal_analysis.disclaimer'),
            ],
        ];
    }
}
