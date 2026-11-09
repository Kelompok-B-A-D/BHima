<?php

namespace App\Services\Analysis;

use App\Contracts\AnalysisProvider;

class MockProvider implements AnalysisProvider
{
    public function name(): string
    {
        return 'mock';
    }

    /**
     * Susun analisis pasal, rujukan, dan estimasi ancaman sanksi murni dari chunk hasil retrieval.
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array{
     *     rujukan: list<array{dokumen: string, nomor_tahun: string, tautan: string}>,
     *     pasal_relevan: list<array{pasal: string, dokumen: string, penjelasan_awam: string, alasan_terkait_kronologi: string}>,
     *     ancaman_sanksi: list<array{pasal: string, jenis: string, bunyi_sanksi_ringkas: string, dokumen: string}>,
     *     perlu_eskalasi: bool,
     *     catatan: string
     * }
     */
    public function analyze(string $chronology, array $chunks): array
    {
        $noSanctionText = (string) config(
            'legal_analysis.no_sanction_text',
            'Tidak tercantum dalam potongan pasal yang ditemukan'
        );

        if (empty($chunks)) {
            return [
                'rujukan' => [],
                'pasal_relevan' => [],
                'ancaman_sanksi' => [],
                'perlu_eskalasi' => true,
                'catatan' => 'Konteks pasal JDIH atau SOP layanan tidak ditemukan untuk kronologi ini. Silakan gunakan tombol Ajukan Pengaduan agar permasalahan Anda ditindaklanjuti langsung oleh petugas verifikator BHIMA.',
            ];
        }

        $rujukan = [];
        $seenDocs = [];
        $pasalRelevan = [];
        $ancamanSanksi = [];
        $hasSanctionInContext = false;

        foreach ($chunks as $chunk) {
            $docName = trim((string) ($chunk['document_name'] ?? ''));
            $docNumber = trim((string) ($chunk['document_number'] ?? ''));
            $docYear = trim((string) ($chunk['document_year'] ?? ''));
            $articleLabel = trim((string) ($chunk['article_label'] ?? ''));
            $sourceUrl = trim((string) ($chunk['source_url'] ?? ''));
            $content = trim((string) ($chunk['content'] ?? ''));
            $topic = trim((string) ($chunk['topic'] ?? 'Layanan Publik'));

            if ($docName === '' || $articleLabel === '' || $content === '') {
                continue;
            }

            $nomorTahun = $this->formatNomorTahun($docNumber, $docYear);
            $docDedupKey = mb_strtolower($docName.'|'.$nomorTahun, 'UTF-8');

            if (! isset($seenDocs[$docDedupKey])) {
                $seenDocs[$docDedupKey] = true;
                $rujukan[] = [
                    'dokumen' => $docName,
                    'nomor_tahun' => $nomorTahun,
                    'tautan' => $sourceUrl,
                ];
            }

            $pasalRelevan[] = [
                'pasal' => $articleLabel,
                'dokumen' => $docName,
                'penjelasan_awam' => $this->buildPlainExplanation($articleLabel, $docName, $content),
                'alasan_terkait_kronologi' => $this->buildRelevanceReason($chronology, $articleLabel, $docName, $topic, $content),
            ];

            $extractedSanction = self::extractSanctionFromChunkContent($content, $noSanctionText);
            if ($extractedSanction['bunyi_sanksi_ringkas'] !== $noSanctionText) {
                $hasSanctionInContext = true;
            }

            $ancamanSanksi[] = [
                'pasal' => $articleLabel,
                'jenis' => $extractedSanction['jenis'],
                'bunyi_sanksi_ringkas' => $extractedSanction['bunyi_sanksi_ringkas'],
                'dokumen' => $docName,
            ];
        }

        $perluEskalasi = $this->shouldEscalate($chronology, $chunks, $hasSanctionInContext);

        $catatan = 'Daftar pasal di atas merupakan pasal yang berpotensi terkait berdasarkan pencocokan kronologi terhadap dokumen JDIH dan SOP layanan, bukan putusan bahwa pelanggaran pasti terjadi. '
            .(string) config('legal_analysis.disclaimer');

        return [
            'rujukan' => $rujukan,
            'pasal_relevan' => $pasalRelevan,
            'ancaman_sanksi' => $ancamanSanksi,
            'perlu_eskalasi' => $perluEskalasi,
            'catatan' => $catatan,
        ];
    }

    /**
     * Ekstrak bunyi sanksi eksplisit HANYA dari teks chunk.
     * Bila tidak terdapat rumusan sanksi eksplisit di dalam teks chunk, kembalikan teks default.
     *
     * @return array{jenis: string, bunyi_sanksi_ringkas: string}
     */
    public static function extractSanctionFromChunkContent(string $content, string $noSanctionText = 'Tidak tercantum dalam potongan pasal yang ditemukan'): array
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $content) ?? '');
        if ($normalized === '') {
            return [
                'jenis' => 'lainnya',
                'bunyi_sanksi_ringkas' => $noSanctionText,
            ];
        }

        // 1. Sanksi Pidana eksplisit (dipidana dengan ..., pidana penjara ..., pidana denda ...)
        if (preg_match('/(dipidana\s+dengan\s+[^.]+(?:\.[^.]+)?)/iu', $normalized, $m)) {
            return [
                'jenis' => 'pidana',
                'bunyi_sanksi_ringkas' => trim($m[1], " .;,"),
            ];
        }

        if (preg_match('/((?:pidana\s+penjara|penjara\s+paling\s+lama|pidana\s+denda)[^.]+)/iu', $normalized, $m)) {
            return [
                'jenis' => 'pidana',
                'bunyi_sanksi_ringkas' => trim($m[1], " .;,"),
            ];
        }

        // 2. Sanksi Administratif eksplisit (dijatuhi Hukuman Disiplin ..., dikenakan denda ..., sanksi administratif ...)
        if (preg_match('/((?:dijatuhi\s+hukuman\s+disiplin|dikenakan\s+sanksi\s+administratif|sanksi\s+administratif\s+berupa|dikenakan\s+denda\s+sesuai)[^.]+)/iu', $normalized, $m)) {
            return [
                'jenis' => 'administratif',
                'bunyi_sanksi_ringkas' => trim($m[1], " .;,"),
            ];
        }

        // 3. Tuntutan / Ganti Rugi Perdata eksplisit (menerima ganti rugi ..., memberikan ganti rugi ...)
        if (preg_match('/((?:berhak\s+menggugat\s+dan\s+menerima\s+ganti\s+rugi|bertanggung\s+jawab\s+memberikan\s+ganti\s+rugi|pemberian\s+ganti\s+rugi\s+berupa)[^.]+)/iu', $normalized, $m)) {
            return [
                'jenis' => 'perdata',
                'bunyi_sanksi_ringkas' => trim($m[1], " .;,"),
            ];
        }

        return [
            'jenis' => 'lainnya',
            'bunyi_sanksi_ringkas' => $noSanctionText,
        ];
    }

    private function formatNomorTahun(string $docNumber, string $docYear): string
    {
        if ($docNumber === '' && $docYear === '') {
            return '-';
        }
        if ($docYear === '' || str_contains(mb_strtolower($docNumber, 'UTF-8'), mb_strtolower($docYear, 'UTF-8'))) {
            return $docNumber;
        }

        return trim($docNumber.' Tahun '.$docYear);
    }

    private function buildPlainExplanation(string $articleLabel, string $docName, string $content): string
    {
        $glossary = $this->buildSimpleTermNote($content);

        return sprintf(
            'Pasal yang berpotensi terkait (%s pada %s): %s%s',
            $articleLabel,
            $docName,
            $content,
            $glossary !== '' ? ' '.$glossary : ''
        );
    }

    private function buildSimpleTermNote(string $content): string
    {
        $lower = mb_strtolower($content, 'UTF-8');
        if (str_contains($lower, 'bipartit')) {
            return '(Istilah bipartit berarti perundingan musyawarah langsung antara pekerja dan pengusaha).';
        }
        if (str_contains($lower, 'gawat darurat')) {
            return '(Gawat darurat berarti kondisi medis mengancam nyawa yang wajib ditangani segera tanpa syarat uang muka).';
        }
        if (str_contains($lower, 'gratifikasi') || str_contains($lower, 'pungutan')) {
            return '(Pungutan di luar ketentuan resmi dilarang dalam pelayanan masyarakat).';
        }
        if (str_contains($lower, 'litigasi') || str_contains($lower, 'nonlitigasi')) {
            return '(Litigasi adalah penanganan di pengadilan, sedangkan nonlitigasi di luar pengadilan seperti mediasi/konsultasi).';
        }

        return '';
    }

    private function buildRelevanceReason(string $chronology, string $articleLabel, string $docName, string $topic, string $content): string
    {
        $snippet = mb_strlen($content) > 140 ? mb_substr($content, 0, 137).'...' : $content;

        return sprintf(
            'Ketentuan %s dalam %s berpotensi terkait dengan kronologi warga pada kategori %s karena memuat aturan: "%s"',
            $articleLabel,
            $docName,
            $topic !== '' ? $topic : 'Layanan Hukum',
            $snippet
        );
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    private function shouldEscalate(string $chronology, array $chunks, bool $hasSanctionInContext): bool
    {
        if (empty($chunks) || $hasSanctionInContext) {
            return true;
        }

        $lower = mb_strtolower($chronology, 'UTF-8');
        $escalationTriggers = [
            'pungli', 'uang rokok', 'uang pelicin', 'dimintai biaya', 'dimintai uang',
            'ditolak', 'uang muka', 'deposit', 'dipecat', 'phk', 'gaji belum dibayar',
            'ijazah ditahan', 'serobot', 'patok', 'rusak', 'berlubang', 'kecelakaan',
            'kdrt', 'kekerasan', 'dipukul', 'ancaman', 'sebar data', 'pinjol', 'lapor',
        ];

        foreach ($escalationTriggers as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        $topScore = (float) ($chunks[0]['score'] ?? 0.0);

        return $topScore < 0.45;
    }
}
