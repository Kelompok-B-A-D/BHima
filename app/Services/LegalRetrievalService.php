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
        'dimintain duit' => 'pungutan liar biaya tidak resmi gratis tidak dipungut biaya',
        'minta duit' => 'pungutan liar biaya tidak resmi gratis tidak dipungut biaya',
        'dimintai duit' => 'pungutan liar biaya tidak resmi gratis tidak dipungut biaya',
        'calo' => 'pungutan liar perantara tidak resmi pelayanan publik',
        'buat ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'bikin ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'urus ktp' => 'penerbitan ktp elektronik administrasi kependudukan disdukcapil gratis',
        'ktp hilang' => 'penerbitan ktp elektronik hilang surat kehilangan kepolisian gratis',
        'ktp ilang' => 'penerbitan ktp elektronik hilang surat kehilangan kepolisian gratis',
        'ktp saya ilang' => 'penerbitan ktp elektronik hilang surat kehilangan kepolisian gratis',
        'ktp rusak' => 'penerbitan ktp elektronik rusak penggantian disdukcapil',
        'pindah domisili' => 'pendaftaran penduduk perpindahan domisili administrasi kependudukan disdukcapil gratis',
        'kartu keluarga' => 'penerbitan kartu keluarga kk administrasi kependudukan',
        'akta lahir' => 'penerbitan akta kelahiran pencatatan sipil disdukcapil',
        'ditolak rumah sakit' => 'gawat darurat fasilitas pelayanan kesehatan dilarang menolak pasien igd',
        'ditolak igd' => 'gawat darurat fasilitas pelayanan kesehatan dilarang menolak pasien igd',
        'ugd' => 'igd gawat darurat fasilitas pelayanan kesehatan rumah sakit pertolongan pertama',
        'serangan jantung' => 'gawat darurat igd penyelamatan nyawa pasien rumah sakit pertolongan pertama',
        'bayar dp' => 'gawat darurat dilarang meminta uang muka deposit pasien igd rumah sakit',
        'minta dp' => 'gawat darurat dilarang meminta uang muka deposit pasien igd rumah sakit',
        'uang muka' => 'gawat darurat dilarang meminta uang muka deposit pasien rumah sakit',
        'deposit rumah sakit' => 'gawat darurat dilarang meminta uang muka deposit pasien rumah sakit',
        'salah memberikan obat' => 'kelalaian tenaga medis kesalahan pemberian obat malapraktik kerugian pasien majelis disiplin profesi',
        'salah kasih obat' => 'kelalaian tenaga medis kesalahan pemberian obat malapraktik kerugian pasien majelis disiplin profesi',
        'malapraktik' => 'kelalaian tenaga medis kealpaan kerugian pasien rumah sakit majelis disiplin profesi',
        'bpjs mati' => 'jaminan kesehatan bpjs pbi nonaktif reaktivasi darurat sktm',
        'bpjs nonaktif' => 'jaminan kesehatan bpjs pbi nonaktif reaktivasi darurat sktm',
        'menunggak iuran' => 'peserta pbpu mandiri tidak membayar iuran diberhentikan sementara menunggak iuran rehab bpjs',
        'jalan rusak' => 'perbaikan jalan rusak berlubang kecelakaan lalu lintas rambu pupr',
        'jalan berlubang' => 'perbaikan jalan rusak berlubang kecelakaan lalu lintas rambu pupr',
        'bolong' => 'perbaikan jalan rusak berlubang kecelakaan lalu lintas penyelenggara jalan pupr',
        'jatoh motor' => 'kecelakaan lalu lintas jalan rusak berlubang korban luka',
        'tabrak lari' => 'kecelakaan lalu lintas pengemudi kendaraan bermotor tidak menghentikan kendaraan tidak memberikan pertolongan tidak melaporkan kepolisian',
        'banjir' => 'drainase jalan tersumbat genangan pemeliharaan pupr lingkungan',
        'dipecat' => 'pemutusan hubungan kerja phk pesangon bipartit mediasi disnaker',
        'phk sepihak' => 'pemutusan hubungan kerja phk pesangon bipartit mediasi disnaker',
        'gaji belum dibayar' => 'keterlambatan pembayaran upah gaji denda pengupahan disnaker',
        'upah belum dibayar' => 'keterlambatan pembayaran upah gaji denda pengupahan disnaker',
        'tidak bayar gaji' => 'keterlambatan pembayaran upah gaji wajib membayar upah tepat waktu pengupahan disnaker',
        'ga bayar gaji' => 'keterlambatan pembayaran upah gaji wajib membayar upah tepat waktu pengupahan disnaker',
        'gaji di bawah umr' => 'dilarang membayar upah lebih rendah dari upah minimum ump umk pengupahan disnaker',
        'di bawah umr' => 'dilarang membayar upah lebih rendah dari upah minimum ump umk pengupahan disnaker',
        'di bawah umk' => 'dilarang membayar upah lebih rendah dari upah minimum ump umk pengupahan disnaker',
        'lembur' => 'melebihi waktu kerja wajib membayar upah kerja lembur pengupahan disnaker',
        'thr' => 'tunjangan hari raya keagamaan wajib dibayarkan pengusaha denda keterlambatan pengupahan',
        'gaji ditahan' => 'keterlambatan pembayaran upah gaji pengupahan disnaker',
        'ijazah ditahan' => 'larangan menahan dokumen asli ijazah pekerja ketenagakerjaan disnaker',
        'tahan ijazah' => 'larangan menahan dokumen asli ijazah pekerja ketenagakerjaan disnaker',
        'asisten rumah tangga' => 'orang yang bekerja membantu rumah tangga menetap lingkup rumah tangga kdrt penelantaran upah',
        'tanah diserobot' => 'sengketa batas bidang tanah penyerobotan patok mediasi pertanahan bpn',
        'serobot tanah' => 'sengketa batas bidang tanah penyerobotan patok mediasi pertanahan bpn',
        'patok tanah' => 'sengketa batas bidang tanah penetapan batas mediasi pertanahan bpn',
        'batas tanah' => 'sengketa batas bidang tanah penetapan batas mediasi pertanahan bpn',
        'melebihi batas' => 'sengketa batas bidang tanah penetapan batas mediasi pertanahan bpn',
        'developer' => 'peralihan hak atas tanah jual beli kavling akta ppat pendaftaran tanah kantor pertanahan tidak menepati pesanan',
        'membagi warisan' => 'ahli waris perselisihan pembagian harta warisan musyawarah kekeluargaan surat keterangan waris',
        'rumah warisan' => 'ahli waris perselisihan pembagian harta warisan musyawarah kekeluargaan surat keterangan waris',
        'sertifikat hilang' => 'sertipikat pengganti hilang pendaftaran tanah kantor pertanahan',
        'sertifikat' => 'sertipikat pendaftaran tanah',
        'pinjol' => 'pinjaman online penagihan ancaman sebar data pribadi ojk',
        'debt collector' => 'penagihan ancaman kekerasan mempermalukan konsumen ojk data pribadi',
        'jam berapa' => 'pukul waktu jadwal',
        'sarana komunikasi' => 'pukul senin sabtu penagihan komunikasi',
        'sebar data' => 'mengungkapkan menyebarkan data pribadi tanpa persetujuan pidana pdp',
        'sebar foto' => 'mengungkapkan menyebarkan data pribadi tanpa persetujuan pidana pdp',
        'menyebarkan foto pribadi' => 'mengungkapkan menyebarkan data pribadi tanpa persetujuan pidana pdp',
        'bantuan hukum' => 'bantuan hukum cuma-cuma gratis masyarakat miskin sktm posbakum obh',
        'tidak mampu bayar pengacara' => 'bantuan hukum cuma-cuma gratis masyarakat miskin sktm posbakum obh',
        'pengacara gratis' => 'bantuan hukum cuma-cuma gratis masyarakat miskin sktm posbakum obh',
        'kdrt' => 'kekerasan dalam rumah tangga fisik psikis perlindungan korban uptd ppa',
        'dipukul suami' => 'kekerasan dalam rumah tangga fisik perlindungan sementara kepolisian uptd ppa',
        'mukulin' => 'kekerasan dalam rumah tangga kdrt fisik perlindungan sementara kepolisian uptd ppa',
        'dibully' => 'kekerasan terhadap anak perundungan bullying sekolah pendampingan psikologis visum uptd ppa',
        'pencabulan' => 'kekerasan seksual perlindungan anak korban uptd ppa',
        'pelecehan seksual' => 'kekerasan seksual perlindungan korban uptd ppa',
        'dana desa' => 'keterbukaan informasi publik transparansi anggaran apbdes pemerintah desa ppid',
        'korupsi dana desa' => 'tindak pidana korupsi menyalahgunakan kewenangan jabatan merugikan keuangan negara dana desa apbdes',
        'kades' => 'kepala desa pemerintah desa transparansi apbdes dana desa',
        'apbdes' => 'keterbukaan informasi publik transparansi anggaran apbdes pemerintah desa ppid',
        'memotong uang bantuan' => 'pungutan liar menyalahgunakan kekuasaan menerima pembayaran dengan potongan bantuan sosial blt',
        'potong bantuan' => 'pungutan liar menyalahgunakan kekuasaan menerima pembayaran dengan potongan bantuan sosial blt',
        'belanja hp' => 'menawarkan barang melalui pesanan marketplace tidak menepati pesanan perlindungan konsumen ganti rugi bpsk',
        'marketplace' => 'menawarkan barang melalui pesanan marketplace tidak menepati pesanan perlindungan konsumen ganti rugi bpsk',
        'barang tidak dikirim' => 'menawarkan barang melalui pesanan tidak menepati pesanan perlindungan konsumen ganti rugi',
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
     * Pola kata kunci yang menunjukkan warga menanyakan pasal atau ancaman sanksi.
     *
     * @var list<string>
     */
    private const SANCTION_QUERY_KEYWORDS = [
        'sanksi', 'sanksinya', 'hukuman', 'hukumannya', 'dipidana', 'pidana',
        'denda', 'penjara', 'kurungan', 'ancaman', 'pasal berapa', 'pasal apa',
        'terancam', 'dihukum', 'jerat', 'dijerat', 'kena pasal',
    ];

    /**
     * Token meta-pertanyaan sanksi/pidana yang tidak boleh menaikkan skor kecocokan substantif lintas topik (BUG-03).
     *
     * @var list<string>
     */
    private const META_SANCTION_TOKENS = [
        'sanksi', 'hukuman', 'hukum', 'pidana', 'dipidana', 'denda', 'penjara',
        'kurungan', 'ancaman', 'terancam', 'dihukum', 'jerat', 'dijerat',
    ];

    /**
     * Token berfrekuensi tinggi lintas domain yang tidak boleh meloloskan dokumen sendirian tanpa kata kunci topik inti (BUG-06).
     *
     * @var list<string>
     */
    private const LOW_SIGNAL_TOKENS = [
        'uang', 'biaya', 'bayar', 'membayar', 'dibayar', 'pembayaran',
        'rumah', 'kantor', 'desa', 'kelurahan', 'kecamatan', 'dinas', 'instansi',
        'rt', 'rw', 'tetangga', 'warga', 'masyarakat', 'penduduk',
        'oknum', 'petugas', 'pejabat', 'pegawai', 'pelaku', 'korban',
        'anak', 'suami', 'istri', 'teman', 'kakak', 'adik', 'pacar',
        'izin', 'surat', 'dokumen', 'kartu', 'data', 'foto', 'pesan',
        'sengketa', 'masalah', 'kasus', 'perkara', 'perjanjian', 'kontrak',
        'batas', 'waktu', 'masa', 'kerja', 'usaha', 'barang', 'bantuan',
        'tolak', 'menolak', 'ditolak', 'lapor', 'melapor', 'melaporkan', 'laporan', 'pengaduan',
        'hilang', 'rusak', 'palsu', 'sepihak', 'online', 'whatsapp', 'grup',
        'hak', 'ajukan', 'mengajukan', 'diajukan', 'gugat', 'menggugat', 'gugatan', 'pengadilan', 'agama', 'telepon',
    ];

    /**
     * Lakukan pencarian hibrida (semantik cosine similarity + kata kunci IDF) terhadap indeks JDIH dan SOP.
     * Menerapkan aturan Bagian 7 panduan JDIH BHIMA:
     * - Dokumen berstatus `dicabut` dikecualikan dari rujukan utama (arsip saja).
     * - Pertanyaan sanksi/pasal memprioritaskan `is_ketentuan_pidana = true`.
     * - Pasal norma yang ditemukan otomatis menarik pasal sanksinya melalui `rujuk_pasal`.
     *
     * @return array{
     *     rewritten_query: string,
     *     topic_hint: ?string,
     *     sanction_query: bool,
     *     revoked_notices: list<string>,
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

        $lowerChronology = mb_strtolower(trim($chronology), 'UTF-8');
        $sanctionQuery = $this->isSanctionQuery($lowerChronology);
        $queryData = $this->summarizeToSearchQuery($chronology);
        $domainSignal = $this->embeddingService->domainSignalScore($queryData['rewritten_query']);

        // Jika sama sekali tidak ada token bermakna
        if (empty($queryData['expanded_tokens'])) {
            return [
                'rewritten_query' => $queryData['rewritten_query'],
                'topic_hint' => null,
                'sanction_query' => $sanctionQuery,
                'revoked_notices' => [],
                'top_k' => $k,
                'threshold' => $threshold,
                'top_score' => 0.0,
                'has_context' => false,
                'chunks' => [],
            ];
        }

        $queryVector = $this->embeddingService->embed($queryData['rewritten_query']);
        $allRows = DB::table('knowledge_chunks')->get();

        // Pisahkan dokumen aktif dan arsip berstatus dicabut (dokumen dicabut dilarang masuk rujukan utama)
        $activeRows = [];
        $revokedDocs = [];
        foreach ($allRows as $row) {
            $statusBerlaku = strtolower(trim((string) ($row->status_berlaku ?? 'berlaku')));
            if ($statusBerlaku === 'dicabut') {
                $dKey = (string) $row->document_key;
                if (! isset($revokedDocs[$dKey])) {
                    $revokedDocs[$dKey] = [
                        'document_key' => $dKey,
                        'document_name' => (string) $row->document_name,
                        'document_number' => (string) $row->document_number,
                        'dicabut_oleh' => trim((string) ($row->dicabut_oleh ?? '')),
                    ];
                }

                continue;
            }
            $activeRows[] = $row;
        }

        $revokedNotices = $this->detectMentionedRevokedRegulations($lowerChronology, $revokedDocs);
        $totalChunks = max(1, count($activeRows));

        // Hitung frekuensi dokumen per token (untuk pembobotan IDF agar kata umum tidak mendominasi skor)
        $docFreq = [];
        $rowWordSets = [];
        foreach ($activeRows as $idx => $row) {
            $chunkTokens = array_filter(explode(' ', (string) $row->keywords), fn (string $t) => $t !== '');
            $contentLower = mb_strtolower(
                $row->document_name.' '.($row->topic ?? '').' '.$row->article_label.' '.$row->content,
                'UTF-8'
            );
            $rawWords = preg_split('/[^a-z0-9]+/u', $contentLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $combinedSet = array_flip(array_merge($chunkTokens, $rawWords));
            $rowWordSets[$idx] = $combinedSet;

            foreach (array_keys($combinedSet) as $tok) {
                $docFreq[$tok] = ($docFreq[$tok] ?? 0) + 1;
            }
        }

        $scoredChunks = [];

        foreach ($activeRows as $idx => $row) {
            if (! $this->isChunkCompatibleWithChronology($row, $lowerChronology, $sanctionQuery)) {
                continue;
            }

            $chunkVector = json_decode((string) $row->embedding, true) ?: [];
            $semanticScore = $this->embeddingService->cosineSimilarity($queryVector, $chunkVector);
            $keywordScore = $this->computeKeywordScore(
                $queryData['original_tokens'],
                $queryData['expanded_tokens'],
                $rowWordSets[$idx],
                $docFreq,
                $totalChunks
            );

            // Cegah false-positive ketika tidak ada kecocokan kata kunci nyata pada teks pasal
            if ($keywordScore <= 0.0) {
                $hybridScore = min(0.20, $semanticScore * 0.20);
            } elseif ($keywordScore < 0.05 && $domainSignal < 0.25) {
                $hybridScore = $semanticScore * 0.20;
            } elseif ($keywordScore < 0.05) {
                $hybridScore = $semanticScore * 0.35;
            } else {
                $hybridScore = ($semanticWeight * $semanticScore) + ($keywordWeight * $keywordScore);
            }

            $isCorruptionOrPungliQuery = $this->containsAny($lowerChronology, ['korupsi', 'pungli', 'pungutan liar', 'suap', 'gratifikasi']);
            $topicMatchesHint = $queryData['topic_hint'] === null
                || strcasecmp((string) $row->topic, $queryData['topic_hint']) === 0
                || ($isCorruptionOrPungliQuery && strcasecmp((string) $row->topic, 'Dugaan Pungutan Liar') === 0);

            // Boost jika topik dokumen selaras dengan topik kronologi
            if ($topicMatchesHint && $queryData['topic_hint'] !== null && $keywordScore >= 0.14) {
                $hybridScore += 0.08;
            }

            // Aturan 7.1: bila pertanyaan memuat kata sanksi/hukuman/dipidana/denda/pasal berapa, prioritaskan ketentuan pidana HANYA jika topik/kata kunci substantifnya cocok (BUG-03)
            $isSanctionChunk = (bool) ($row->is_ketentuan_pidana ?? false);
            if ($sanctionQuery && $isSanctionChunk && $keywordScore >= 0.18 && $hybridScore >= 0.25 && $topicMatchesHint) {
                $hybridScore += 0.12;
            }

            // Boost untuk regulasi utama (UU/PP/Perpres/Perda & SOP resmi) hanya bila sudah benar-benar relevan
            $isPrimary = $this->isPrimaryReference((string) $row->document_key);
            if ($keywordScore >= 0.20 && $hybridScore >= 0.24 && $isPrimary) {
                $hybridScore += 0.14;
            }

            $hybridScore = min(1.0, round($hybridScore, 4));

            if ($hybridScore >= $threshold) {
                $scoredChunks[] = $this->buildChunkArray(
                    $row,
                    $hybridScore,
                    round($semanticScore, 4),
                    round($keywordScore, 4),
                    $isPrimary
                );
            }
        }

        usort(
            $scoredChunks,
            fn (array $a, array $b) => ($b['score'] <=> $a['score'])
                ?: ((int) ($sanctionQuery && $b['is_ketentuan_pidana']) <=> (int) ($sanctionQuery && $a['is_ketentuan_pidana']))
                ?: ($b['_is_primary'] <=> $a['_is_primary'])
        );

        // Terapkan batas relatif terhadap skor tertinggi agar pasal lemah dari topik lain tidak ikut terbawa
        $topRawScore = ! empty($scoredChunks) ? (float) $scoredChunks[0]['score'] : 0.0;
        $relativeFloor = $topRawScore >= 0.60
            ? max($threshold, round($topRawScore * 0.58, 4))
            : $threshold;

        // Pilih top-k dengan keberagaman dokumen (maks 2 chunk per dokumen), tanpa duplikat pasal/isi
        $selected = [];
        $perDocCount = [];
        $seenArticleKeys = [];
        $seenContentHashes = [];
        foreach ($scoredChunks as $candidate) {
            if ($candidate['score'] < $relativeFloor) {
                break;
            }
            $articleDedup = $candidate['document_key'].'|'.mb_strtolower($candidate['article_label'], 'UTF-8');
            $contentDedup = sha1(mb_strtolower(preg_replace('/\s+/u', ' ', trim($candidate['content'])) ?? '', 'UTF-8'));
            if (isset($seenArticleKeys[$articleDedup]) || isset($seenContentHashes[$contentDedup])) {
                continue;
            }
            $dKey = $candidate['document_key'];
            $perDocCount[$dKey] = ($perDocCount[$dKey] ?? 0) + 1;
            if ($perDocCount[$dKey] > 2) {
                continue;
            }
            $seenArticleKeys[$articleDedup] = true;
            $seenContentHashes[$contentDedup] = true;
            $selected[] = $candidate;
            if (count($selected) >= $k) {
                break;
            }
        }

        // Aturan 7.2: setiap pasal norma otomatis menarik pasal sanksinya via rujuk_pasal (dan sebaliknya)
        $selected = $this->attachLinkedSanctionAndNormChunks($selected, $activeRows, $k, $sanctionQuery, $lowerChronology);

        $hasSopInSelected = collect($selected)->contains(fn (array $c) => $c['category'] === 'SOP');

        if (! $hasSopInSelected && count($selected) >= 2) {
            $primaryTopic = $selected[0]['topic'] ?? null;
            $sopMinScore = max($relativeFloor, 0.35);
            $bestSop = collect($scoredChunks)->first(
                fn (array $c) => $c['category'] === 'SOP'
                    && $c['score'] >= $sopMinScore
                    && $c['keyword_score'] >= 0.15
                    && (
                        $primaryTopic === null
                        || strcasecmp($c['topic'], $primaryTopic) === 0
                        || ($queryData['topic_hint'] !== null && strcasecmp($c['topic'], $queryData['topic_hint']) === 0)
                    )
            );
            if ($bestSop !== null) {
                // Jangan menimpa pasal sanksi tertaut jika slot sudah penuh
                $replaceIdx = count($selected) - 1;
                for ($i = count($selected) - 1; $i >= 1; $i--) {
                    if (empty($selected[$i]['_linked_via_rujuk_pasal']) && ! ($sanctionQuery && $selected[$i]['is_ketentuan_pidana'])) {
                        $replaceIdx = $i;
                        break;
                    }
                }
                if (empty($selected[$replaceIdx]['_linked_via_rujuk_pasal'])) {
                    $selected[$replaceIdx] = $bestSop;
                    usort(
                        $selected,
                        fn (array $a, array $b) => ($b['score'] <=> $a['score']) ?: ($b['_is_primary'] <=> $a['_is_primary'])
                    );
                }
            }
        }

        $selected = array_map(static function (array $item): array {
            unset($item['_is_primary'], $item['_linked_via_rujuk_pasal']);

            return $item;
        }, $selected);

        $topScore = ! empty($selected) ? (float) $selected[0]['score'] : 0.0;

        if (! empty($selected) && $queryData['topic_hint'] !== null) {
            $this->recordPublicSearchTerm($queryData['topic_hint']);
        }

        return [
            'rewritten_query' => $queryData['rewritten_query'],
            'topic_hint' => $queryData['topic_hint'],
            'sanction_query' => $sanctionQuery,
            'revoked_notices' => $revokedNotices,
            'top_k' => $k,
            'threshold' => $threshold,
            'top_score' => $topScore,
            'has_context' => ! empty($selected),
            'chunks' => array_values($selected),
        ];
    }

    /**
     * Hitung skor kecocokan kata kunci utuh berbasis bobot IDF dengan pembedaan kata sinyal tinggi vs kata generik/meta-sanksi (BUG-03 & BUG-06).
     *
     * @param  list<string>  $originalTokens
     * @param  list<string>  $expandedTokens
     * @param  array<string, int>  $chunkWordSet
     * @param  array<string, int>  $docFreq
     */
    private function computeKeywordScore(
        array $originalTokens,
        array $expandedTokens,
        array $chunkWordSet,
        array $docFreq,
        int $totalChunks
    ): float {
        if (empty($expandedTokens)) {
            return 0.0;
        }

        $metaSet = array_flip(self::META_SANCTION_TOKENS);
        $lowSignalSet = array_flip(self::LOW_SIGNAL_TOKENS);

        $matchedWeight = 0.0;
        $maxPossible = 0.0;
        $exactHits = 0;
        $highSignalHits = 0;
        $lowSignalHits = 0;

        foreach ($originalTokens as $token) {
            $df = $docFreq[$token] ?? 0;
            $idfFactor = max(0.35, min(2.0, log(($totalChunks + 1) / ($df + 1)) / 2.5));

            if (isset($metaSet[$token])) {
                $weight = 0.15 * $idfFactor;
            } elseif (isset($lowSignalSet[$token])) {
                $weight = 0.45 * $idfFactor;
            } else {
                $weight = 1.65 * $idfFactor;
            }

            $maxPossible += $weight;
            if (isset($chunkWordSet[$token])) {
                $matchedWeight += $weight;
                $exactHits++;
                if (! isset($metaSet[$token])) {
                    if (isset($lowSignalSet[$token])) {
                        $lowSignalHits++;
                    } else {
                        $highSignalHits++;
                    }
                }
            }
        }

        $expansionOnly = array_diff($expandedTokens, $originalTokens);
        foreach ($expansionOnly as $token) {
            $df = $docFreq[$token] ?? 0;
            $idfFactor = max(0.35, min(2.0, log(($totalChunks + 1) / ($df + 1)) / 2.5));

            if (isset($metaSet[$token])) {
                $weight = 0.10 * $idfFactor;
            } elseif (isset($lowSignalSet[$token])) {
                $weight = 0.30 * $idfFactor;
            } else {
                $weight = 1.15 * $idfFactor;
            }

            $maxPossible += $weight;
            if (isset($chunkWordSet[$token])) {
                $matchedWeight += $weight;
                $exactHits++;
                if (! isset($metaSet[$token])) {
                    if (isset($lowSignalSet[$token])) {
                        $lowSignalHits++;
                    } else {
                        $highSignalHits++;
                    }
                }
            }
        }

        if ($exactHits === 0 || $maxPossible <= 0.0) {
            return 0.0;
        }

        // BUG-03 & BUG-06: Jika chunk hanya cocok pada kata meta-sanksi (pidana/penjara/denda) atau < 3 kata umum tanpa kata kunci domain inti, tolak
        if ($highSignalHits === 0 && $lowSignalHits < 3) {
            return 0.0;
        }

        // Normalisasi agar kronologi panjang tidak menurunkan skor secara tidak proporsional
        $effectiveDenominator = min($maxPossible, 10.0);
        $ratio = min(1.0, $matchedWeight / $effectiveDenominator);

        if ($highSignalHits >= 3 && $exactHits >= 4) {
            $ratio = min(1.0, $ratio + 0.15);
        } elseif ($highSignalHits >= 2 && $exactHits >= 2) {
            $ratio = min(1.0, $ratio + 0.07);
        }

        return round($ratio, 4);
    }

    /**
     * Filter kompatibilitas domain & sub-topik agar pasal spesifik tidak bocor ke konteks kasus yang salah (BUG-03, BUG-05, BUG-06, BUG-08).
     */
    private function isChunkCompatibleWithChronology(object $row, string $lowerChronology, bool $sanctionQuery): bool
    {
        $docKey = strtoupper(trim((string) $row->document_key));
        $articleLabel = mb_strtolower(trim((string) $row->article_label), 'UTF-8');

        // 1. BUG-05: UU KDRT (JDIH-019) hanya berlaku untuk kekerasan/penelantaran dalam lingkup rumah tangga
        if ($docKey === 'JDIH-019') {
            $hasViolenceOrNeglect = $this->containsAny($lowerChronology, [
                'kdrt', 'kekerasan', 'seksual', 'pelecehan', 'pencabulan', 'cabul',
                'dipukul', 'memukul', 'mukulin', 'kasar', 'aniaya', 'penganiayaan',
                'penelantaran', 'menelantarkan', 'melarang', 'uptd', 'visum', 'rumah aman',
            ]);
            if (! $hasViolenceOrNeglect) {
                return false;
            }

            $hasHouseholdContext = $this->containsAny($lowerChronology, [
                'kdrt', 'rumah tangga', 'suami', 'istri', 'isteri', 'asisten rumah tangga', 'art', 'majikan',
            ]);
            $hasNonHouseholdContext = $this->containsAny($lowerChronology, [
                'atasan', 'kantor', 'tempat saya bekerja', 'teman sekelas', 'sekolah',
                'smp', 'sma', 'pacar', 'parkir', 'tetangga',
            ]);
            if ($hasNonHouseholdContext && ! $hasHouseholdContext) {
                return false;
            }
        }

        // 2. BUG-05: SOP-010 (UPTD PPA) hanya berlaku jika ada unsur kekerasan/pelecehan/perundungan/KDRT/PPA (bukan sengketa parkir/waris/cerai selingkuh/batas rumah/PBG)
        if ($docKey === 'SOP-010') {
            $hasPpaSignal = $this->containsAny($lowerChronology, [
                'kdrt', 'kekerasan', 'seksual', 'pelecehan', 'pencabulan', 'cabul',
                'dipukul', 'memukul', 'mukulin', 'kasar', 'aniaya', 'bully', 'dibully',
                'perundungan', 'uptd', 'ppa', 'visum', 'rumah aman', 'asisten rumah tangga', 'melarang keluar',
            ]);
            if (! $hasPpaSignal) {
                return false;
            }
            if ($this->containsAny($lowerChronology, ['parkir', 'dikeroyok tetangga'])) {
                return false;
            }
        }

        // 3. BUG-08: Sub-topik Pelayanan Kesehatan (JDIH-006, JDIH-007, SOP-004, SOP-014)
        if ($docKey === 'SOP-014') {
            return $this->containsAny($lowerChronology, ['ambulans', 'evakuasi', 'rujukan terpadu', 'jemput pasien']);
        }

        $isMalpracticeQuery = $this->containsAny($lowerChronology, [
            'salah memberikan obat', 'salah kasih obat', 'salah obat', 'malapraktik', 'alergi', 'kelalaian', 'kealpaan',
        ]);
        $isEmergencyQuery = $this->containsAny($lowerChronology, [
            'igd', 'ugd', 'gawat', 'darurat', 'uang muka', 'deposit', 'bayar dp', 'minta dp', 'serangan jantung', 'pertolongan pertama',
        ]);

        if ($docKey === 'JDIH-006') {
            $isIgdArticle = str_starts_with($articleLabel, 'pasal 174') || str_starts_with($articleLabel, 'pasal 438');
            $isMalpracticeArticle = str_starts_with($articleLabel, 'pasal 193')
                || str_starts_with($articleLabel, 'pasal 305')
                || str_starts_with($articleLabel, 'pasal 440');

            if ($isMalpracticeQuery && ! $isEmergencyQuery && $isIgdArticle) {
                return false;
            }
            if ($isMalpracticeArticle && ! $isMalpracticeQuery) {
                return false;
            }
        }

        if ($isMalpracticeQuery && ! $isEmergencyQuery && in_array($docKey, ['JDIH-007', 'SOP-004'], true)) {
            return false;
        }

        $isBpjsArrearsQuery = $this->containsAny($lowerChronology, ['menunggak', 'tunggakan', 'mandiri', 'pbpu', 'rehab']);
        if ($docKey === 'JDIH-007') {
            $isArrearsArticle = str_starts_with($articleLabel, 'pasal 42');
            if ($isArrearsArticle && ! $isBpjsArrearsQuery) {
                return false;
            }
            if ($isBpjsArrearsQuery && str_starts_with($articleLabel, 'pasal 8') && ! $this->containsAny($lowerChronology, ['pbi', 'fakir', 'miskin', 'tidak mampu'])) {
                return false;
            }
        }
        if ($docKey === 'SOP-004' && $isBpjsArrearsQuery && ! $isEmergencyQuery && ! $this->containsAny($lowerChronology, ['pbi', 'tidak mampu', 'sktm'])) {
            return false;
        }

        // 4. BUG-08: Sub-topik Lalu Lintas & Jalan Rusak (JDIH-008 & SOP-005)
        $isHitAndRunQuery = $this->containsAny($lowerChronology, ['tabrak lari', 'ditabrak', 'menabrak', 'kabur']);
        $isDamagedRoadQuery = $this->containsAny($lowerChronology, ['jalan rusak', 'berlubang', 'bolong', 'penyelenggara jalan', 'rambu', 'dinas', 'drainase', 'banjir']);
        if ($docKey === 'JDIH-008') {
            $isHitAndRunArticle = str_starts_with($articleLabel, 'pasal 231') || str_starts_with($articleLabel, 'pasal 312');

            if ($isHitAndRunArticle && ! $isHitAndRunQuery) {
                return false;
            }
            if ($isHitAndRunQuery && ! $isDamagedRoadQuery && ! $isHitAndRunArticle) {
                return false;
            }
        }
        if ($docKey === 'SOP-005' && $isHitAndRunQuery && ! $isDamagedRoadQuery) {
            return false;
        }

        // 5. BUG-08: Sub-topik Ketenagakerjaan (JDIH-012, JDIH-013, JDIH-014)
        if ($docKey === 'JDIH-012') {
            if (str_starts_with($articleLabel, 'pasal 15') && ! $this->containsAny($lowerChronology, ['pkwt', 'kontrak', 'kompensasi'])) {
                return false;
            }
            if (str_starts_with($articleLabel, 'pasal 31') && ! $this->containsAny($lowerChronology, ['lembur', 'waktu kerja'])) {
                return false;
            }
            $isPhkArticle = str_starts_with($articleLabel, 'pasal 37')
                || str_starts_with($articleLabel, 'pasal 39')
                || str_starts_with($articleLabel, 'pasal 40');
            if ($isPhkArticle && ! $this->containsAny($lowerChronology, ['phk', 'dipecat', 'pemutusan', 'pesangon', 'bipartit'])) {
                return false;
            }
        }

        if ($docKey === 'JDIH-013') {
            $isThrArticle = str_starts_with($articleLabel, 'pasal 9') || str_starts_with($articleLabel, 'pasal 78');
            if ($isThrArticle && ! $this->containsAny($lowerChronology, ['thr', 'tunjangan hari raya', 'lebaran'])) {
                return false;
            }
            if (str_starts_with($articleLabel, 'pasal 65') && ! $this->containsAny($lowerChronology, ['potong', 'pemotongan', 'dipotong'])) {
                return false;
            }
            if (str_starts_with($articleLabel, 'pasal 79') && (! $sanctionQuery || ! $this->containsAny($lowerChronology, ['umr', 'umk', 'ump', 'minimum', 'lebih rendah']))) {
                return false;
            }
        }

        if ($docKey === 'JDIH-014' && str_starts_with($articleLabel, 'pasal 28')) {
            $hasHarassmentSignal = $this->containsAny($lowerChronology, ['pelecehan', 'seksual', 'kesusilaan']);
            $hasWorkplaceContext = $this->containsAny($lowerChronology, ['atasan', 'kantor', 'bekerja', 'tempat kerja', 'perusahaan', 'karyawan', 'pekerja']);
            if (! $hasHarassmentSignal || ! $hasWorkplaceContext) {
                return false;
            }
        }

        // 6. BUG-08: Sub-topik Pertanahan & Waris (JDIH-015, SOP-008, SOP-018)
        if ($docKey === 'SOP-018') {
            return $this->containsAny($lowerChronology, ['waris', 'warisan', 'pewaris', 'peninggalan', 'wasiat', 'hibah']);
        }
        $isDeveloperPurchaseQuery = $this->containsAny($lowerChronology, ['developer', 'pengembang', 'kavling', 'jual beli', 'ajb', 'ppat']);
        if ($docKey === 'JDIH-015') {
            if (str_starts_with($articleLabel, 'pasal 57') && ! $this->containsAny($lowerChronology, ['hilang', 'ilang', 'rusak', 'kehilangan', 'pengganti'])) {
                return false;
            }
            $isPurchaseArticle = str_starts_with($articleLabel, 'pasal 37') || str_starts_with($articleLabel, 'pasal 40');
            if ($isPurchaseArticle && ! $isDeveloperPurchaseQuery) {
                return false;
            }
            if ($isDeveloperPurchaseQuery && str_starts_with($articleLabel, 'pasal 18') && ! $this->containsAny($lowerChronology, ['batas', 'patok', 'serobot', 'tetangga'])) {
                return false;
            }
        }
        if ($docKey === 'SOP-008' && $isDeveloperPurchaseQuery && ! $this->containsAny($lowerChronology, ['batas', 'patok', 'serobot', 'tetangga', 'desa', 'kelurahan'])) {
            return false;
        }

        // 7. BUG-06 & BUG-08: Sub-topik Perlindungan Konsumen, Data Pribadi, Pinjol, dan POJK (JDIH-021, JDIH-017, SOP-009, JDIH-018)
        if ($docKey === 'JDIH-017') {
            $hasPersonalDataSignal = $this->containsAny($lowerChronology, [
                'data pribadi', 'data', 'pdp', 'sebar', 'menyebarkan', 'mengungkapkan',
                'foto pribadi', 'foto ktp', 'kontak', 'pinjol', 'privasi', 'bocor',
            ]);
            if (! $hasPersonalDataSignal) {
                return false;
            }
        }
        if ($docKey === 'JDIH-021') {
            $isOrderFraudArticle = str_starts_with($articleLabel, 'pasal 16') || str_starts_with($articleLabel, 'pasal 62 ayat (2)');
            $isOrderFraudQuery = $this->containsAny($lowerChronology, ['pesanan', 'marketplace', 'belanja', 'dikirim', 'ditransfer', 'developer', 'kavling']);
            if ($isOrderFraudArticle && ! $isOrderFraudQuery) {
                return false;
            }
            if (str_starts_with($articleLabel, 'pasal 62 ayat (1)') && (! $sanctionQuery || ! $this->containsAny($lowerChronology, ['cacat', 'rusak', 'ganti rugi', 'toko', 'konsumen', 'barang']))) {
                return false;
            }
        }

        if (in_array($docKey, ['SOP-009', 'JDIH-018'], true)) {
            $hasFintechOrPdpSignal = $this->containsAny($lowerChronology, [
                'pinjol', 'pinjaman online', 'fintech', 'ojk', 'collector', 'penagih',
                'penagihan', 'kredit', 'jasa keuangan', 'data pribadi', 'sebar data', 'sebar foto',
            ]);
            if (! $hasFintechOrPdpSignal) {
                return false;
            }
        }

        // 8. BUG-03: UU Tipikor (JDIH-005) hanya untuk konteks korupsi, suap, gratifikasi, pungli, atau pemotongan oleh pejabat/aparat/desa (bukan limbah pabrik atau gaji UMR swasta)
        if ($docKey === 'JDIH-005') {
            $hasTipikorContext = $this->containsAny($lowerChronology, [
                'korupsi', 'suap', 'gratifikasi', 'pungli', 'pungutan', 'pemerasan', 'peras',
                'uang rokok', 'uang pelicin', 'potongan', 'memotong', 'potong', 'dana desa',
                'apbdes', 'apbd', 'anggaran', 'pegawai negeri', 'pns', 'pejabat', 'penyelenggara negara',
                'kepala desa', 'kades', 'petugas', 'oknum', 'kelurahan', 'kecamatan', 'disdukcapil', 'rt', 'rw',
            ]);
            if (! $hasTipikorContext) {
                return false;
            }
            if (str_starts_with($articleLabel, 'pasal 3') && ! $this->containsAny($lowerChronology, ['korupsi', 'dana desa', 'apbdes', 'apbd', 'anggaran', 'keuangan negara', 'kepala desa', 'kades'])) {
                return false;
            }
        }

        // 9. BUG-06: Pemotongan Bansos/BLT tidak boleh menyeret SOP BPJS (SOP-004), Posbakum (SOP-006), atau SKTM (SOP-013) kecuali ditanyakan
        $isBansosCutQuery = $this->containsAny($lowerChronology, ['blt', 'bansos', 'bantuan sosial'])
            && $this->containsAny($lowerChronology, ['potong', 'memotong', 'dipotong']);
        if ($isBansosCutQuery && in_array($docKey, ['SOP-004', 'SOP-006', 'SOP-013', 'JDIH-010', 'JDIH-011'], true)) {
            if (! $this->containsAny($lowerChronology, ['pengacara', 'advokat', 'posbakum', 'sktm', 'bpjs', 'rumah sakit'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $tokens
     */
    private function detectTopicHint(string $lowerText, array $tokens): ?string
    {
        $tokenSet = array_flip($tokens);
        $rawWordSet = array_flip(preg_split('/[^a-z0-9]+/u', $lowerText, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $topicRules = [
            'Dugaan Pungutan Liar' => ['pungli', 'pungutan', 'rokok', 'pelicin', 'suap', 'pemerasan', 'peras', 'calo', 'amplop', 'saber', 'korupsi'],
            'Administrasi Kependudukan' => ['ktp', 'ktpel', 'kk', 'akta', 'dukcapil', 'disdukcapil', 'nik', 'kependudukan', 'domisili'],
            'Pelayanan Kesehatan' => ['igd', 'ugd', 'rsud', 'puskesmas', 'bpjs', 'pasien', 'darurat', 'medis', 'dokter', 'rawat', 'ambulans', 'malapraktik'],
            'Infrastruktur' => ['jalan', 'berlubang', 'bolong', 'jembatan', 'drainase', 'banjir', 'pupr', 'rambu', 'aspal', 'limbah', 'pencemaran', 'tabrak'],
            'Ketenagakerjaan' => ['phk', 'dipecat', 'pesangon', 'upah', 'gaji', 'ijazah', 'buruh', 'pekerja', 'disnaker', 'pkwt', 'lembur', 'thr', 'umr', 'umk'],
            'Pertanahan' => ['tanah', 'sertifikat', 'sertipikat', 'shm', 'patok', 'serobot', 'diserobot', 'bpn', 'agraria', 'girik', 'waris', 'warisan', 'kavling', 'developer'],
            'Perlindungan Konsumen & Digital' => ['pinjol', 'collector', 'ojk', 'konsumen', 'cacat', 'garansi', 'bpsk', 'marketplace'],
            'Perlindungan Perempuan & Anak' => ['kdrt', 'kekerasan', 'seksual', 'pencabulan', 'cabul', 'pelecehan', 'uptd', 'visum', 'dibully', 'perundungan'],
            'Keterbukaan Informasi & Anggaran' => ['apbdes', 'apbd', 'anggaran', 'ppid', 'transparansi', 'bansos', 'blt'],
            'Bantuan Hukum' => ['posbakum', 'obh', 'sktm', 'advokat', 'pengacara', 'pendampingan'],
        ];

        $bestTopic = null;
        $bestHits = 0;

        foreach ($topicRules as $topic => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) {
                $stem = $this->embeddingService->stemToken($kw);
                if (isset($tokenSet[$kw]) || isset($tokenSet[$stem]) || isset($rawWordSet[$kw])) {
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

    public function isSanctionQuery(string $lowerText): bool
    {
        foreach (self::SANCTION_QUERY_KEYWORDS as $kw) {
            if (str_contains($lowerText, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Deteksi bila warga menanyakan secara eksplisit peraturan yang sudah berstatus dicabut di arsip.
     *
     * @param  array<string, array{document_key: string, document_name: string, document_number: string, dicabut_oleh: string}>  $revokedDocs
     * @return list<string>
     */
    private function detectMentionedRevokedRegulations(string $lowerChronology, array $revokedDocs): array
    {
        if (empty($revokedDocs)) {
            return [];
        }

        $notices = [];
        foreach ($revokedDocs as $doc) {
            $matched = false;
            $docNumberLower = mb_strtolower($doc['document_number'], 'UTF-8');
            if ($docNumberLower !== '' && str_contains($lowerChronology, $docNumberLower)) {
                $matched = true;
            }

            // Cocokkan pola singkat seperti "perpres 87/2016", "perpres 87 tahun 2016", "uu 31/2014", "saber pungli"
            if (! $matched && preg_match('/\b(uu|pp|perpres|permen|perda|pojk)\s*(?:no\.?\s*)?(\d+)\s*(?:tahun\s*|\/)\s*(\d{4})\b/iu', $docNumberLower, $m)) {
                $shortA = $m[1].' '.$m[2].'/'.$m[3];
                $shortB = $m[1].' '.$m[2].' tahun '.$m[3];
                $shortC = $m[1].' nomor '.$m[2].' tahun '.$m[3];
                if (str_contains($lowerChronology, $shortA)
                    || str_contains($lowerChronology, $shortB)
                    || str_contains($lowerChronology, $shortC)
                ) {
                    $matched = true;
                }
            }

            if (! $matched && str_contains($docNumberLower, '87 tahun 2016') && str_contains($lowerChronology, 'saber pungli')) {
                $matched = true;
            }

            if ($matched) {
                $replacement = $doc['dicabut_oleh'] !== ''
                    ? " oleh {$doc['dicabut_oleh']}"
                    : '';
                $notices[] = sprintf(
                    'Catatan arsip: %s (%s) berstatus dicabut%s sehingga tidak lagi digunakan sebagai rujukan utama.',
                    $doc['document_name'],
                    $doc['document_number'],
                    $replacement
                );
            }
        }

        return array_values(array_unique($notices));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChunkArray(
        object $row,
        float $score,
        float $semanticScore,
        float $keywordScore,
        bool $isPrimary,
        bool $linkedViaRujuk = false
    ): array {
        return [
            'chunk_id' => (int) $row->id,
            'document_key' => (string) $row->document_key,
            'document_name' => (string) $row->document_name,
            'document_type' => (string) $row->document_type,
            'document_number' => (string) $row->document_number,
            'document_year' => (int) $row->document_year,
            'article_label' => (string) $row->article_label,
            'pasal_nomor' => isset($row->pasal_nomor) ? (string) $row->pasal_nomor : null,
            'ayat' => isset($row->ayat) ? (string) $row->ayat : null,
            'is_ketentuan_pidana' => (bool) ($row->is_ketentuan_pidana ?? false),
            'sanksi_jenis' => isset($row->sanksi_jenis) ? (string) $row->sanksi_jenis : null,
            'sanksi_teks' => isset($row->sanksi_teks) ? (string) $row->sanksi_teks : null,
            'rujuk_pasal' => isset($row->rujuk_pasal) ? (string) $row->rujuk_pasal : null,
            'status_berlaku' => (string) ($row->status_berlaku ?? 'berlaku'),
            'diubah_oleh' => isset($row->diubah_oleh) ? (string) $row->diubah_oleh : null,
            'dicabut_oleh' => isset($row->dicabut_oleh) ? (string) $row->dicabut_oleh : null,
            'source_url' => (string) $row->source_url,
            'source_page_url' => isset($row->source_page_url) ? (string) $row->source_page_url : null,
            'pdf_page' => isset($row->pdf_page) ? (int) $row->pdf_page : null,
            'category' => (string) $row->category,
            'topic' => (string) ($row->topic ?? 'Umum'),
            'content' => (string) $row->content,
            'score' => $score,
            'semantic_score' => $semanticScore,
            'keyword_score' => $keywordScore,
            '_is_primary' => $isPrimary ? 1 : 0,
            '_linked_via_rujuk_pasal' => $linkedViaRujuk,
        ];
    }

    /**
     * Aturan 7.2: setiap pasal norma yang ditemukan otomatis menarik pasal sanksinya lewat rujuk_pasal,
     * dan pasal sanksi yang ditemukan juga menarik pasal norma yang dirujuknya bila belum ada di hasil.
     *
     * @param  list<array<string, mixed>>  $selected
     * @param  list<object>  $activeRows
     * @return list<array<string, mixed>>
     */
    private function attachLinkedSanctionAndNormChunks(
        array $selected,
        array $activeRows,
        int $k,
        bool $sanctionQuery,
        string $lowerChronology = ''
    ): array {
        if (empty($selected) || empty($activeRows)) {
            return $selected;
        }

        $rowsByDoc = [];
        foreach ($activeRows as $row) {
            $rowsByDoc[(string) $row->document_key][] = $row;
        }

        $existingChunkIds = array_flip(array_column($selected, 'chunk_id'));
        $linkedToAdd = [];

        foreach ($selected as $idx => $item) {
            // BUG-02: Hanya tarik pasal tertaut jika pasal induknya berada di posisi teratas atau memiliki skor kecocokan kuat
            if ($idx > 1 && ((float) $item['score'] < 0.48 || (float) $item['keyword_score'] < 0.16)) {
                continue;
            }
            if ((float) $item['score'] < 0.42 || (float) $item['keyword_score'] < 0.12) {
                continue;
            }

            $dKey = (string) $item['document_key'];
            $docRows = $rowsByDoc[$dKey] ?? [];
            if (empty($docRows)) {
                continue;
            }

            // 1. Jika chunk adalah pasal norma (memiliki pasal_nomor), tarik pasal sanksi yang merujuknya via rujuk_pasal
            $pasalNomor = trim((string) ($item['pasal_nomor'] ?? ''));
            if ($pasalNomor === '' && preg_match('/\bPasal\s+(\d+[A-Z]?)\b/iu', (string) $item['article_label'], $pm)) {
                $pasalNomor = strtoupper(trim($pm[1]));
            }

            if ($pasalNomor !== '') {
                $normPattern = '/\bPasal\s+'.preg_quote($pasalNomor, '/').'\b/iu';
                foreach ($docRows as $candidateRow) {
                    $cid = (int) $candidateRow->id;
                    if (isset($existingChunkIds[$cid]) || isset($linkedToAdd[$cid])) {
                        continue;
                    }
                    if ($lowerChronology !== '' && ! $this->isChunkCompatibleWithChronology($candidateRow, $lowerChronology, $sanctionQuery)) {
                        continue;
                    }
                    $rujuk = trim((string) ($candidateRow->rujuk_pasal ?? ''));
                    if ((bool) ($candidateRow->is_ketentuan_pidana ?? false) && $rujuk !== '' && preg_match($normPattern, $rujuk)) {
                        $linkedScore = min(1.0, round(max(0.26, (float) $item['score'] - 0.01), 4));
                        $linkedToAdd[$cid] = $this->buildChunkArray(
                            $candidateRow,
                            $linkedScore,
                            (float) $item['semantic_score'],
                            (float) $item['keyword_score'],
                            $this->isPrimaryReference($dKey),
                            true
                        );
                    }
                }
            }

            // 2. Jika chunk adalah pasal sanksi yang merujuk pasal norma via rujuk_pasal, tarik pasal norma terkaitnya
            $rujukPasal = trim((string) ($item['rujuk_pasal'] ?? ''));
            if ($rujukPasal !== '' && preg_match_all('/\bPasal\s+(\d+[A-Z]?)\b/iu', $rujukPasal, $rm)) {
                foreach (array_unique($rm[1]) as $refPasalNum) {
                    foreach ($docRows as $candidateRow) {
                        $cid = (int) $candidateRow->id;
                        if (isset($existingChunkIds[$cid]) || isset($linkedToAdd[$cid])) {
                            continue;
                        }
                        if ($lowerChronology !== '' && ! $this->isChunkCompatibleWithChronology($candidateRow, $lowerChronology, $sanctionQuery)) {
                            continue;
                        }
                        $candNum = trim((string) ($candidateRow->pasal_nomor ?? ''));
                        if ($candNum !== '' && strcasecmp($candNum, $refPasalNum) === 0) {
                            $linkedScore = min(1.0, round(max(0.26, (float) $item['score'] - 0.02), 4));
                            $linkedToAdd[$cid] = $this->buildChunkArray(
                                $candidateRow,
                                $linkedScore,
                                (float) $item['semantic_score'],
                                (float) $item['keyword_score'],
                                $this->isPrimaryReference($dKey),
                                true
                            );
                            break;
                        }
                    }
                }
            }
        }

        foreach ($linkedToAdd as $linkedChunk) {
            if (count($selected) < $k) {
                $selected[] = $linkedChunk;

                continue;
            }

            // Gantikan slot paling bawah yang bukan pasal tertaut dan bukan ketentuan pidana prioritas
            $replaceIdx = null;
            for ($i = count($selected) - 1; $i >= 1; $i--) {
                $isProtected = ! empty($selected[$i]['_linked_via_rujuk_pasal'])
                    || ($sanctionQuery && ! empty($selected[$i]['is_ketentuan_pidana']));
                if (! $isProtected) {
                    $replaceIdx = $i;
                    break;
                }
            }

            if ($replaceIdx !== null) {
                $selected[$replaceIdx] = $linkedChunk;
            }
        }

        usort(
            $selected,
            fn (array $a, array $b) => ($b['score'] <=> $a['score'])
                ?: ((int) ($sanctionQuery && $b['is_ketentuan_pidana']) <=> (int) ($sanctionQuery && $a['is_ketentuan_pidana']))
                ?: ($b['_is_primary'] <=> $a['_is_primary'])
        );

        return array_values($selected);
    }

    private function isPrimaryReference(string $docKey): bool
    {
        if (str_starts_with($docKey, 'SOP-')) {
            return true;
        }
        if (preg_match('/^JDIH-(\d+)$/', $docKey, $m)) {
            return (int) $m[1] <= 25;
        }

        return str_starts_with(strtoupper($docKey), 'JDIH-');
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
