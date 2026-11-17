<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LegalRetrievalService
{
    /**
     * Kamus ekspansi ungkapan bahasa awam warga ke terminologi hukum & SOP layanan.
     *
     * @var array<string, string>
     */
    private const COLLOQUIAL_EXPANSIONS = [
        'uang rokok' => 'pungutan liar biaya tidak resmi gratis saber pungli',
        'uang pelicin' => 'pungutan liar biaya tidak resmi suap saber pungli',
        'uang kopi' => 'pungutan liar biaya tidak resmi gratis',
        'biaya tambahan' => 'pungutan liar biaya tidak resmi gratis',
        'minta uang' => 'pungutan liar biaya tidak resmi pemerasan',
        'dimintai uang' => 'pungutan liar biaya tidak resmi pemerasan',
        'dimintai biaya' => 'pungutan liar biaya tidak resmi gratis',
        'calo' => 'pungutan liar perantara tidak resmi pelayanan publik',
        'buat ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'bikin ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'urus ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'ktp hilang' => 'penerbitan ktp elektronik hilang surat kehilangan kepolisian',
        'ktp rusak' => 'penerbitan ktp elektronik rusak penggantian disdukcapil',
        'kartu keluarga' => 'penerbitan kartu keluarga kk administrasi kependudukan',
        'akta lahir' => 'penerbitan akta kelahiran pencatatan sipil disdukcapil',
        'ditolak rumah sakit' => 'gawat darurat fasilitas pelayanan kesehatan dilarang menolak pasien igd',
        'ditolak igd' => 'gawat darurat fasilitas pelayanan kesehatan dilarang menolak pasien igd',
        'uang muka' => 'gawat darurat dilarang meminta uang muka deposit pasien rumah sakit',
        'deposit rumah sakit' => 'gawat darurat dilarang meminta uang muka deposit pasien rumah sakit',
        'bpjs mati' => 'jaminan kesehatan bpjs pbi nonaktif reaktivasi darurat sktm',
        'bpjs nonaktif' => 'jaminan kesehatan bpjs pbi nonaktif reaktivasi darurat sktm',
        'jalan rusak' => 'perbaikan jalan rusak berlubang kecelakaan lalu lintas rambu pupr',
        'jalan berlubang' => 'perbaikan jalan rusak berlubang kecelakaan lalu lintas rambu pupr',
        'banjir' => 'drainase jalan tersumbat genangan pemeliharaan pupr lingkungan',
        'dipecat' => 'pemutusan hubungan kerja phk pesangon bipartit mediasi disnaker',
        'phk sepihak' => 'pemutusan hubungan kerja phk pesangon bipartit mediasi disnaker',
        'gaji belum dibayar' => 'keterlambatan pembayaran upah gaji denda pengupahan disnaker',
        'upah belum dibayar' => 'keterlambatan pembayaran upah gaji denda pengupahan disnaker',
        'gaji ditahan' => 'keterlambatan pembayaran upah gaji pengupahan disnaker',
        'ijazah ditahan' => 'larangan menahan dokumen asli ijazah pekerja ketenagakerjaan disnaker',
        'tahan ijazah' => 'larangan menahan dokumen asli ijazah pekerja ketenagakerjaan disnaker',
        'tanah diserobot' => 'sengketa batas bidang tanah penyerobotan patok mediasi pertanahan bpn',
        'serobot tanah' => 'sengketa batas bidang tanah penyerobotan patok mediasi pertanahan bpn',
        'patok tanah' => 'sengketa batas bidang tanah penetapan batas mediasi pertanahan bpn',
        'batas tanah' => 'sengketa batas bidang tanah penetapan batas mediasi pertanahan bpn',
        'sertifikat hilang' => 'sertipikat pengganti hilang pendaftaran tanah kantor pertanahan',
        'pinjol' => 'pinjaman online penagihan ancaman sebar data pribadi ojk',
        'debt collector' => 'penagihan ancaman kekerasan mempermalukan konsumen ojk data pribadi',
        'sebar data' => 'mengungkapkan menyebarkan data pribadi tanpa persetujuan pidana pdp',
        'bantuan hukum' => 'bantuan hukum cuma-cuma gratis masyarakat miskin sktm posbakum obh',
        'tidak mampu bayar pengacara' => 'bantuan hukum cuma-cuma gratis masyarakat miskin sktm posbakum obh',
        'kdrt' => 'kekerasan dalam rumah tangga fisik psikis perlindungan korban uptd ppa',
        'dipukul suami' => 'kekerasan dalam rumah tangga fisik perlindungan sementara kepolisian uptd ppa',
        'dana desa' => 'keterbukaan informasi publik transparansi anggaran apbdes pemerintah desa ppid',
        'apbdes' => 'keterbukaan informasi publik transparansi anggaran apbdes pemerintah desa ppid',
        'barang rusak' => 'perlindungan konsumen ganti rugi barang cacat bpsk',
        'barang cacat' => 'perlindungan konsumen ganti rugi barang cacat bpsk',
    ];

    public function __construct(
        private readonly EmbeddingService $embeddingService
    ) {}

    /**
     * Ringkas dan ubah kronologi warga menjadi kueri pencarian terstruktur.
     *
     * @return array{rewritten_query: string, original_tokens: list<string>, expanded_tokens: list<string>, topic_hint: ?string}
     */
    public function summarizeToSearchQuery(string $chronology): array
    {
        $lower = mb_strtolower(trim($chronology), 'UTF-8');
        $expansions = [];

        foreach (self::COLLOQUIAL_EXPANSIONS as $phrase => $mappedTerms) {
            if (str_contains($lower, $phrase)) {
                $expansions[] = $mappedTerms;
            }
        }

        $originalTokens = array_values(array_unique($this->embeddingService->tokenize($chronology)));
        $expansionText = implode(' ', $expansions);
        $expandedTokens = array_values(array_unique(array_merge(
            $originalTokens,
            $this->embeddingService->tokenize($expansionText)
        )));

        $coreTerms = array_slice($originalTokens, 0, 18);
        $extraTerms = array_diff($expandedTokens, $coreTerms);
        $queryParts = array_merge($coreTerms, array_slice($extraTerms, 0, 12));

        $rewrittenQuery = trim(implode(' ', $queryParts));
        if ($rewrittenQuery === '') {
            $rewrittenQuery = mb_substr(trim(preg_replace('/\s+/u', ' ', $chronology) ?? ''), 0, 200);
        }

        return [
            'rewritten_query' => $rewrittenQuery,
            'original_tokens' => $originalTokens,
            'expanded_tokens' => $expandedTokens,
            'topic_hint' => $this->detectTopicHint($lower, $expandedTokens),
        ];
    }

    /**
     * Lakukan pencarian hibrida (semantik cosine similarity + kata kunci) terhadap indeks 100 dokumen JDIH dan SOP.
     *
     * @return array{
     *     rewritten_query: string,
     *     topic_hint: ?string,
     *     top_k: int,
     *     threshold: float,
     *     top_score: float,
     *     has_context: bool,
     *     chunks: list<array<string, mixed>>
     * }
     */
    public function retrieve(string $chronology, ?int $topK = null, ?float $minSimilarity = null): array
    {
        $k = max(1, min(15, $topK ?? (int) config('rag.retrieval.top_k', 5)));
        $threshold = $minSimilarity ?? (float) config('rag.retrieval.min_similarity', 0.26);
        $semanticWeight = (float) config('rag.retrieval.semantic_weight', 0.60);
        $keywordWeight = (float) config('rag.retrieval.keyword_weight', 0.40);

        $queryData = $this->summarizeToSearchQuery($chronology);
        $domainSignal = $this->embeddingService->domainSignalScore($queryData['rewritten_query']);

        // Jika sama sekali tidak ada token bermakna
        if (empty($queryData['expanded_tokens'])) {
            return [
                'rewritten_query' => $queryData['rewritten_query'],
                'topic_hint' => null,
                'top_k' => $k,
                'threshold' => $threshold,
                'top_score' => 0.0,
                'has_context' => false,
                'chunks' => [],
            ];
        }

        $queryVector = $this->embeddingService->embed($queryData['rewritten_query']);
        $rows = DB::table('knowledge_chunks')->get();

        $scoredChunks = [];

        foreach ($rows as $row) {
            $chunkVector = json_decode((string) $row->embedding, true) ?: [];
            $semanticScore = $this->embeddingService->cosineSimilarity($queryVector, $chunkVector);
            $keywordScore = $this->computeKeywordScore(
                $queryData['original_tokens'],
                $queryData['expanded_tokens'],
                (string) $row->keywords,
                (string) $row->content,
                (string) $row->document_name,
                (string) $row->article_label,
                (string) ($row->topic ?? '')
            );

            // Cegah false-positive dari hash tabrakan acak ketika tidak ada irisan kata kunci maupun sinyal domain hukum
            if ($keywordScore < 0.05 && $domainSignal < 0.25) {
                $hybridScore = $semanticScore * 0.20;
            } elseif ($keywordScore < 0.04) {
                $hybridScore = $semanticScore * 0.45;
            } else {
                $hybridScore = ($semanticWeight * $semanticScore) + ($keywordWeight * $keywordScore);
            }

            // Boost jika topik dokumen selaras dengan topik kronologi
            if ($queryData['topic_hint'] !== null && strcasecmp((string) $row->topic, $queryData['topic_hint']) === 0 && $keywordScore >= 0.08) {
                $hybridScore += 0.08;
            }

            // Boost untuk regulasi utama (UU/PP/Perpres/Perda inti & SOP utama) bila relevan
            if ($keywordScore >= 0.10 && $this->isPrimaryReference((string) $row->document_key)) {
                $hybridScore += 0.14;
            }

            $hybridScore = min(1.0, round($hybridScore, 4));

            if ($hybridScore >= $threshold) {
                $scoredChunks[] = [
                    'chunk_id' => (int) $row->id,
                    'document_key' => (string) $row->document_key,
                    'document_name' => (string) $row->document_name,
                    'document_type' => (string) $row->document_type,
                    'document_number' => (string) $row->document_number,
                    'document_year' => (int) $row->document_year,
                    'article_label' => (string) $row->article_label,
                    'source_url' => (string) $row->source_url,
                    'category' => (string) $row->category,
                    'topic' => (string) ($row->topic ?? 'Umum'),
                    'content' => (string) $row->content,
                    'score' => $hybridScore,
                    'semantic_score' => round($semanticScore, 4),
                    'keyword_score' => round($keywordScore, 4),
                ];
            }
        }

        usort($scoredChunks, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        // Pilih top-k dengan keberagaman dokumen (maks 2 chunk per dokumen) serta pastikan keterwakilan SOP
        $selected = [];
        $perDocCount = [];
        foreach ($scoredChunks as $candidate) {
            $dKey = $candidate['document_key'];
            $perDocCount[$dKey] = ($perDocCount[$dKey] ?? 0) + 1;
            if ($perDocCount[$dKey] > 2) {
                continue;
            }
            $selected[] = $candidate;
            if (count($selected) >= $k) {
                break;
            }
        }
        $hasSopInSelected = collect($selected)->contains(fn (array $c) => $c['category'] === 'SOP');

        if (! $hasSopInSelected && count($selected) >= 2) {
            $bestSop = collect($scoredChunks)->first(fn (array $c) => $c['category'] === 'SOP');
            if ($bestSop !== null) {
                $selected[count($selected) - 1] = $bestSop;
                usort($selected, fn (array $a, array $b) => $b['score'] <=> $a['score']);
            }
        }

        $topScore = ! empty($selected) ? (float) $selected[0]['score'] : 0.0;

        if (! empty($selected) && $queryData['topic_hint'] !== null) {
            $this->recordPublicSearchTerm($queryData['topic_hint']);
        }

        return [
            'rewritten_query' => $queryData['rewritten_query'],
            'topic_hint' => $queryData['topic_hint'],
            'top_k' => $k,
            'threshold' => $threshold,
            'top_score' => $topScore,
            'has_context' => ! empty($selected),
            'chunks' => array_values($selected),
        ];
    }

    /**
     * Hitung skor kecocokan kata kunci (BM25-style lexical overlap).
     *
     * @param  list<string>  $originalTokens
     * @param  list<string>  $expandedTokens
     */
    private function computeKeywordScore(
        array $originalTokens,
        array $expandedTokens,
        string $chunkKeywords,
        string $chunkContent,
        string $docName,
        string $articleLabel,
        string $topic
    ): float {
        if (empty($expandedTokens)) {
            return 0.0;
        }

        $chunkTokenSet = array_flip(explode(' ', $chunkKeywords));
        $contentLower = mb_strtolower($docName.' '.$topic.' '.$articleLabel.' '.$chunkContent, 'UTF-8');

        $matchedWeight = 0.0;
        $maxPossible = 0.0;
        $exactHits = 0;

        foreach ($originalTokens as $token) {
            $maxPossible += 1.5;
            if (isset($chunkTokenSet[$token]) || str_contains($contentLower, $token)) {
                $matchedWeight += 1.5;
                $exactHits++;
            }
        }

        $expansionOnly = array_diff($expandedTokens, $originalTokens);
        foreach ($expansionOnly as $token) {
            $maxPossible += 1.0;
            if (isset($chunkTokenSet[$token]) || str_contains($contentLower, $token)) {
                $matchedWeight += 1.0;
                $exactHits++;
            }
        }

        if ($exactHits === 0 || $maxPossible <= 0.0) {
            return 0.0;
        }

        // Normalisasi agar kronologi panjang tidak menurunkan skor secara tidak proporsional
        $effectiveDenominator = min($maxPossible, 10.0);
        $ratio = min(1.0, $matchedWeight / $effectiveDenominator);

        if ($exactHits >= 4) {
            $ratio = min(1.0, $ratio + 0.15);
        } elseif ($exactHits >= 2) {
            $ratio = min(1.0, $ratio + 0.07);
        }

        return round($ratio, 4);
    }

    /**
     * @param  list<string>  $tokens
     */
    private function detectTopicHint(string $lowerText, array $tokens): ?string
    {
        $tokenSet = array_flip($tokens);

        $topicRules = [
            'Dugaan Pungutan Liar' => ['pungli', 'pungutan', 'rokok', 'pelicin', 'suap', 'pemerasan', 'peras', 'calo', 'amplop', 'saber'],
            'Administrasi Kependudukan' => ['ktp', 'ktpel', 'kk', 'akta', 'dukcapil', 'disdukcapil', 'nik', 'kependudukan', 'domisili'],
            'Pelayanan Kesehatan' => ['igd', 'ugd', 'rsud', 'puskesmas', 'bpjs', 'pasien', 'darurat', 'medis', 'dokter', 'rawat', 'ambulans'],
            'Infrastruktur' => ['jalan', 'berlubang', 'jembatan', 'drainase', 'banjir', 'pupr', 'rambu', 'aspal', 'limbah', 'pencemaran'],
            'Ketenagakerjaan' => ['phk', 'dipecat', 'pesangon', 'upah', 'gaji', 'ijazah', 'buruh', 'pekerja', 'disnaker', 'pkwt', 'lembur'],
            'Pertanahan' => ['tanah', 'sertifikat', 'shm', 'patok', 'batas', 'serobot', 'diserobot', 'bpn', 'agraria', 'girik', 'waris'],
            'Perlindungan Konsumen & Digital' => ['pinjol', 'collector', 'ojk', 'konsumen', 'cacat', 'garansi', 'bpsk', 'penipuan', 'scam'],
            'Perlindungan Perempuan & Anak' => ['kdrt', 'kekerasan', 'suami', 'istri', 'anak', 'pelecehan', 'uptd', 'visum'],
            'Keterbukaan Informasi & Anggaran' => ['apbdes', 'apbd', 'anggaran', 'ppid', 'transparansi', 'bansos', 'blt'],
            'Bantuan Hukum' => ['posbakum', 'obh', 'sktm', 'advokat', 'pengacara', 'pendampingan'],
        ];

        $bestTopic = null;
        $bestHits = 0;

        foreach ($topicRules as $topic => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) {
                $stem = $this->embeddingService->stemToken($kw);
                if (isset($tokenSet[$kw]) || isset($tokenSet[$stem]) || str_contains($lowerText, $kw)) {
                    $hits++;
                }
            }
            if ($hits > $bestHits) {
                $bestHits = $hits;
                $bestTopic = $topic;
            }
        }

        return $bestHits > 0 ? $bestTopic : null;
    }

    private function isPrimaryReference(string $docKey): bool
    {
        if (str_starts_with($docKey, 'SOP-')) {
            return true;
        }
        if (preg_match('/^JDIH-(\d+)$/', $docKey, $m)) {
            return (int) $m[1] <= 25;
        }

        return false;
    }

    private function recordPublicSearchTerm(string $term): void
    {
        try {
            $key = 'SEARCH-'.strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $term) ?? 'UMUM');
            $existing = DB::table('dashboard_records')
                ->where('collection', 'searches')
                ->where('record_key', $key)
                ->first();

            $count = 1;
            if ($existing) {
                $payload = json_decode((string) $existing->payload, true) ?: [];
                $count = ((int) ($payload['count'] ?? 0)) + 1;
            }

            DB::table('dashboard_records')->updateOrInsert(
                ['collection' => 'searches', 'record_key' => $key],
                [
                    'payload' => json_encode(['term' => $term, 'count' => $count], JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => $existing?->created_at ?? now(),
                ]
            );
        } catch (\Throwable) {
            // Pencatatan statistik bersifat non-blocking
        }
    }
}
