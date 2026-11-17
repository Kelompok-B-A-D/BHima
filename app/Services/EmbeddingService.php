<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    /**
     * Kata-kata umum (stopwords) dan kata struktural regulasi yang tidak memiliki bobot topik spesifik.
     */
    private const STOPWORDS = [
        'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'dengan', 'untuk', 'pada', 'adalah', 'sebagai',
        'dalam', 'atau', 'oleh', 'tidak', 'bukan', 'akan', 'telah', 'sudah', 'dapat', 'bisa', 'harus',
        'wajib', 'jika', 'apabila', 'bila', 'maka', 'karena', 'sebab', 'tentang', 'terhadap', 'antara',
        'secara', 'setiap', 'semua', 'seluruh', 'suatu', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'saya', 'aku', 'kami', 'kita', 'anda', 'kamu', 'dia', 'mereka', 'beliau', 'pak', 'bu', 'bapak',
        'ibu', 'kak', 'halo', 'tolong', 'mohon', 'mau', 'ingin', 'tanya', 'bertanya', 'jelaskan',
        'bagaimana', 'apa', 'apakah', 'siapa', 'kapan', 'dimana', 'mengapa', 'kenapa', 'berapa',
        'pasal', 'ayat', 'huruf', 'angka', 'bagian', 'bab', 'paragraf', 'undang', 'peraturan', 'daerah',
        'pemerintah', 'menteri', 'presiden', 'bupati', 'walikota', 'gubernur', 'nomor', 'tahun',
        'sebagaimana', 'dimaksud', 'ketentuan', 'ayat', 'berikut', 'tersebut', 'lainnya', 'lebih',
        'lanjut', 'diatur', 'sesuai', 'berdasarkan', 'melalui', 'kepada', 'bagi', 'sampai', 'hingga',
        'sejak', 'saat', 'ketika', 'kemarin', 'hari', 'bulan', 'minggu', 'lalu', 'tadi', 'sangat',
        'sekali', 'masih', 'belum', 'lagi', 'juga', 'hanya', 'saja', 'pun', 'agar', 'supaya',
    ];

    /**
     * Klaster konsep hukum & layanan publik (dimensi 0..25) untuk menjembatani bahasa awam warga dan bahasa regulasi.
     */
    private const CONCEPT_CLUSTERS = [
        0 => ['ktp', 'ktpel', 'nik', 'kependudukan', 'dukcapil', 'disdukcapil', 'blanko', 'perekaman', 'identitas', 'penduduk', 'domisili', 'pindah', 'adminduk'],
        1 => ['kk', 'keluarga', 'akta', 'kelahiran', 'kematian', 'perkawinan', 'perceraian', 'catatan', 'sipil', 'kelahiran', 'bayi'],
        2 => ['gratis', 'bebas', 'biaya', 'dipungut', '79a', '95b', 'cuma'],
        3 => ['pungli', 'pungutan', 'liar', 'rokok', 'pelicin', 'kopi', 'amplop', 'calo', 'oknum', 'peras', 'pemerasan', 'suap', 'korupsi', 'saber', 'imbalan', 'tip'],
        4 => ['pelayanan', 'publik', 'maklumat', 'standar', 'waktu', 'terlambat', 'maladministrasi', 'ombudsman', 'kompensasi', 'loket', 'antrean', 'prosedur', 'sop'],
        5 => ['kesehatan', 'rumah', 'sakit', 'rsud', 'puskesmas', 'klinik', 'dokter', 'perawat', 'medis', 'rawat', 'inap', 'obat', 'ambulans', 'pasien'],
        6 => ['igd', 'ugd', 'gawat', 'darurat', 'kritis', 'tolak', 'menolak', 'penolakan', 'uang', 'muka', 'deposit', 'nyawa', 'darurat'],
        7 => ['bpjs', 'kis', 'jkn', 'jaminan', 'pbi', 'iuran', 'tunggakan', 'nonaktif', 'faskes', 'rujukan', 'kepesertaan'],
        8 => ['jalan', 'rusak', 'berlubang', 'aspal', 'jembatan', 'rambu', 'penerangan', 'pju', 'pupr', 'jatuh', 'kecelakaan', 'lalu', 'lintas', 'perbaikan'],
        9 => ['drainase', 'selokan', 'got', 'banjir', 'genangan', 'sampah', 'limbah', 'pencemaran', 'polusi', 'bau', 'sungai', 'lingkungan', 'amdal'],
        10 => ['bantuan', 'hukum', 'posbakum', 'obh', 'advokat', 'pengacara', 'miskin', 'sktm', 'pendampingan', 'litigasi', 'nonlitigasi', 'perkara'],
        11 => ['kerja', 'pekerja', 'buruh', 'karyawan', 'perusahaan', 'pabrik', 'majikan', 'phk', 'dipecat', 'pemutusan', 'pesangon', 'pkwt', 'kontrak', 'disnaker', 'bipartit', 'industrial'],
        12 => ['upah', 'gaji', 'umk', 'ump', 'minimum', 'lembur', 'thr', 'dipotong', 'menunggak', 'ijazah', 'ditahan', 'penahanan'],
        13 => ['tanah', 'lahan', 'sertifikat', 'shm', 'hgb', 'bpn', 'agraria', 'batas', 'patok', 'diserobot', 'serobot', 'sengketa', 'girik', 'ukur', 'ptsl', 'ppat'],
        14 => ['waris', 'warisan', 'ahli', 'wasiat', 'peninggalan', 'hibah', 'pembagian'],
        15 => ['konsumen', 'pelaku', 'usaha', 'cacat', 'garansi', 'ganti', 'rugi', 'bpsk', 'komplain', 'kadaluarsa', 'produk'],
        16 => ['pinjol', 'pinjaman', 'online', 'fintech', 'ojk', 'collector', 'penagih', 'teror', 'sebar', 'data', 'pribadi', 'bunga', 'ilegal', 'pdp', 'utang', 'hutang'],
        17 => ['penipuan', 'tipu', 'scam', 'phishing', 'rekening', 'transfer', 'ite', 'pencemaran', 'ancaman', 'elektronik', 'peretasan', 'siber'],
        18 => ['kdrt', 'kekerasan', 'rumah', 'tangga', 'suami', 'istri', 'anak', 'fisik', 'psikis', 'penelantaran', 'pelecehan', 'seksual', 'uptd', 'ppa', 'visum'],
        19 => ['informasi', 'ppid', 'transparansi', 'anggaran', 'apbd', 'apbdes', 'desa', 'keberatan', 'komisi', 'keuangan', 'bansos', 'blt', 'realisasi', 'pagu'],
        20 => ['pelapor', 'saksi', 'whistleblower', 'anonim', 'rahasia', 'intimidasi', 'ancaman', 'perlindungan', 'lpsk', 'kerahasiaan'],
        21 => ['pengaduan', 'laporan', 'tiket', 'verifikasi', 'verifikator', 'disposisi', 'tindak', 'kelengkapan', 'bukti', 'kronologi', 'bhima'],
        22 => ['izin', 'nib', 'oss', 'umkm', 'pedagang', 'ketertiban', 'satpol', 'retribusi', 'pajak', 'imb', 'pbg'],
        23 => ['sanksi', 'teguran', 'disiplin', 'pns', 'asn', 'pejabat', 'pemberhentian', 'inspektorat', 'etik'],
        24 => ['pidana', 'penjara', 'kurungan', 'denda', 'kepolisian', 'penyidik', 'polisi', 'kejahatan'],
        25 => ['mediasi', 'musyawarah', 'damai', 'mediator', 'kesepakatan', 'kelurahan', 'kecamatan'],
    ];

    /**
     * Hasilkan vektor embedding untuk teks.
     *
     * @return list<float>
     */
    public function embed(string $text): array
    {
        $provider = config('rag.embedding.provider', 'local');
        $apiKey = config('rag.embedding.api_key');

        if ($provider === 'api' && ! empty($apiKey)) {
            try {
                $response = Http::withToken($apiKey)
                    ->timeout((int) config('rag.embedding.timeout', 10))
                    ->post(rtrim((string) config('rag.embedding.base_url'), '/').'/embeddings', [
                        'model' => config('rag.embedding.model', 'text-embedding-3-small'),
                        'input' => mb_substr($text, 0, 4000),
                    ]);

                if ($response->successful()) {
                    $vector = $response->json('data.0.embedding');
                    if (is_array($vector) && count($vector) > 0) {
                        return array_map('floatval', $vector);
                    }
                }

                Log::warning('Embedding API gagal atau format tidak sesuai, beralih ke local embedding.', [
                    'status' => $response->status(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Koneksi Embedding API mengalami kendala, menggunakan local embedding.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->embedLocal($text);
    }

    /**
     * Pembangkitan vektor embedding lokal berdimensi tetap (default 128) dengan normalisasi L2.
     *
     * @return list<float>
     */
    public function embedLocal(string $text): array
    {
        $dims = max(64, (int) config('rag.embedding.dimensions', 128));
        $vector = array_fill(0, $dims, 0.0);

        $tokens = $this->tokenize($text);
        if (empty($tokens)) {
            return $vector;
        }

        $tokenFreq = array_count_values($tokens);
        $clusterCount = count(self::CONCEPT_CLUSTERS);

        // 1. Proyeksi ke dimensi klaster konsep hukum (0 .. $clusterCount - 1)
        foreach (self::CONCEPT_CLUSTERS as $clusterIdx => $clusterWords) {
            if ($clusterIdx >= $dims) {
                break;
            }
            $score = 0.0;
            foreach ($clusterWords as $word) {
                $stem = $this->stemToken($word);
                if (isset($tokenFreq[$word])) {
                    $score += 2.2 * (1.0 + log($tokenFreq[$word]));
                } elseif (isset($tokenFreq[$stem])) {
                    $score += 1.8 * (1.0 + log($tokenFreq[$stem]));
                }
            }
            $vector[$clusterIdx] = $score;
        }

        // 2. Proyeksi unigram & bigram dengan signed feature hashing pada dimensi sisanya
        $hashSpace = $dims - $clusterCount;
        $uniqueTokens = array_keys($tokenFreq);

        foreach ($tokenFreq as $token => $count) {
            $weight = 1.0 + log($count);
            $hash = crc32('uni:'.$token);
            $bucket = $clusterCount + ($hash % $hashSpace);
            $sign = (($hash >> 16) & 1) === 0 ? 1.0 : -1.0;
            $vector[$bucket] += $sign * $weight;
        }

        $tokenList = array_values($tokens);
        for ($i = 0; $i < count($tokenList) - 1; $i++) {
            $bigram = $tokenList[$i].'_'.$tokenList[$i + 1];
            $hash = crc32('bi:'.$bigram);
            $bucket = $clusterCount + ($hash % $hashSpace);
            $sign = (($hash >> 16) & 1) === 0 ? 1.0 : -1.0;
            $vector[$bucket] += $sign * 0.85;
        }

        return $this->normalize($vector);
    }

    /**
     * Hitung kemiripan kosinus (cosine similarity) antara dua vektor.
     *
     * @param  list<float>  $vecA
     * @param  list<float>  $vecB
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $len = min(count($vecA), count($vecB));
        if ($len === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $len; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];
            $dot += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 1e-12 || $normB <= 1e-12) {
            return 0.0;
        }

        $similarity = $dot / (sqrt($normA) * sqrt($normB));

        return max(0.0, min(1.0, round($similarity, 4)));
    }

    /**
     * Tokenisasi dan stemming ringan Bahasa Indonesia untuk pencarian & embedding.
     *
     * @return list<string>
     */
    public function tokenize(string $text): array
    {
        $clean = mb_strtolower($text, 'UTF-8');
        $clean = str_replace(['ktp-el', 'e-ktp', 'ektp'], 'ktp elektronik', $clean);
        $clean = preg_replace('/[^a-z0-9\s]/u', ' ', $clean) ?? '';
        $rawParts = preg_split('/\s+/u', trim($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopLookup = array_flip(self::STOPWORDS);
        $tokens = [];

        foreach ($rawParts as $word) {
            if (mb_strlen($word) < 2 || ctype_digit($word)) {
                continue;
            }
            if (isset($stopLookup[$word])) {
                continue;
            }
            $stemmed = $this->stemToken($word);
            if (mb_strlen($stemmed) < 2 || isset($stopLookup[$stemmed])) {
                continue;
            }
            $tokens[] = $stemmed;
        }

        return $tokens;
    }

    /**
     * Hitung seberapa kuat teks berkaitan dengan kosakata domain hukum/layanan publik.
     */
    public function domainSignalScore(string $text): float
    {
        $tokens = $this->tokenize($text);
        if (empty($tokens)) {
            return 0.0;
        }

        $tokenSet = array_flip($tokens);
        $matches = 0;

        foreach (self::CONCEPT_CLUSTERS as $words) {
            foreach ($words as $w) {
                $stem = $this->stemToken($w);
                if (isset($tokenSet[$w]) || isset($tokenSet[$stem])) {
                    $matches++;
                }
            }
        }

        return min(1.0, $matches / 2.0);
    }

    public function stemToken(string $word): string
    {
        if (mb_strlen($word) <= 4) {
            return $word;
        }

        // Hapus partikel & kata ganti milik di akhir
        $word = preg_replace('/(lah|kah|tah|pun)$/u', '', $word) ?? $word;
        if (mb_strlen($word) > 5) {
            $word = preg_replace('/(nya|mu|ku)$/u', '', $word) ?? $word;
        }

        // Hapus akhiran derivasional umum
        if (mb_strlen($word) > 6) {
            $word = preg_replace('/(kan|an)$/u', '', $word) ?? $word;
        }

        // Hapus awalan pasif/intransitif umum tanpa memotong kata dasar seperti sertifikat, sengketa, keluarga, disnaker
        $protectedRoots = [
            'sertifikat', 'sertipikat', 'sengketa', 'serobot', 'sebar', 'keluarga', 'kesehatan',
            'kecelakaan', 'kematian', 'kelahiran', 'ketenagakerjaan', 'keberatan', 'kebijakan',
            'disnaker', 'disdukcapil', 'disposisi', 'deposit', 'dokumen', 'darurat', 'drainase',
            'perusahaan', 'peraturan', 'perkara', 'perjanjian', 'permohonan', 'perlindungan',
        ];
        if (mb_strlen($word) > 7 && ! in_array($word, $protectedRoots, true)) {
            $word = preg_replace('/^(ber|ter)/u', '', $word) ?? $word;
        }

        return $word;
    }

    /**
     * @param  list<float>  $vector
     * @return list<float>
     */
    private function normalize(array $vector): array
    {
        $sumSq = 0.0;
        foreach ($vector as $val) {
            $sumSq += $val * $val;
        }

        if ($sumSq <= 1e-12) {
            return $vector;
        }

        $norm = sqrt($sumSq);

        return array_map(fn (float $v) => round($v / $norm, 6), $vector);
    }
}
