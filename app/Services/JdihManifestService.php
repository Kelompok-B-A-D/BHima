<?php

namespace App\Services;

class JdihManifestService
{
    /**
     * Lokasi manifest prioritas (private storage dulu, lalu database/data/jdih_manifest.csv).
     */
    public function resolveManifestPath(?string $customPath = null): ?string
    {
        if ($customPath !== null && is_file($customPath)) {
            return $customPath;
        }

        $privateManifest = storage_path('app/private/jdih/jdih_manifest.csv');
        if (is_file($privateManifest)) {
            return $privateManifest;
        }

        $repoManifest = database_path('data/jdih_manifest.csv');
        if (is_file($repoManifest)) {
            return $repoManifest;
        }

        return null;
    }

    /**
     * Muat seluruh entri manifest JDIH diindeks berdasarkan doc_key.
     *
     * @return array<string, array{
     *     doc_key: string,
     *     jenis: string,
     *     nomor: string,
     *     tahun: int,
     *     judul: string,
     *     kategori: string,
     *     status: string,
     *     diubah_oleh: string,
     *     dicabut_oleh: string,
     *     url_halaman: string,
     *     url_pdf: string,
     *     tanggal_verifikasi: string,
     *     verifikator: string
     * }>
     */
    public function loadAll(?string $customPath = null): array
    {
        $path = $this->resolveManifestPath($customPath);
        if ($path === null) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $entries = [];
        $headers = null;

        try {
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                if ($headers === null) {
                    $headers = array_map(
                        fn ($h) => strtolower(trim(ltrim((string) $h, "\xEF\xBB\xBF"))),
                        $row
                    );

                    continue;
                }

                if (count($row) < 6) {
                    continue;
                }

                $assoc = [];
                foreach ($headers as $idx => $col) {
                    $assoc[$col] = trim((string) ($row[$idx] ?? ''));
                }

                $docKey = strtoupper($assoc['doc_key'] ?? '');
                if ($docKey === '') {
                    continue;
                }

                $status = strtolower($assoc['status'] ?? 'berlaku');
                if (! in_array($status, ['berlaku', 'diubah', 'dicabut'], true)) {
                    $status = 'berlaku';
                }

                $entries[$docKey] = [
                    'doc_key' => $docKey,
                    'jenis' => $assoc['jenis'] ?? 'Peraturan',
                    'nomor' => $assoc['nomor'] ?? '',
                    'tahun' => (int) ($assoc['tahun'] ?? 0),
                    'judul' => $assoc['judul'] ?? $docKey,
                    'kategori' => $assoc['kategori'] ?? 'Umum',
                    'status' => $status,
                    'diubah_oleh' => $assoc['diubah_oleh'] ?? '',
                    'dicabut_oleh' => $assoc['dicabut_oleh'] ?? '',
                    'url_halaman' => $assoc['url_halaman'] ?? '',
                    'url_pdf' => $assoc['url_pdf'] ?? '',
                    'tanggal_verifikasi' => $assoc['tanggal_verifikasi'] ?? '',
                    'verifikator' => $assoc['verifikator'] ?? '',
                ];
            }
        } finally {
            fclose($handle);
        }

        return $entries;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $docKey, ?string $customPath = null): ?array
    {
        $all = $this->loadAll($customPath);

        return $all[strtoupper(trim($docKey))] ?? null;
    }

    /**
     * Cari berkas PDF resmi lokal di storage/app/private/jdih/ (atau direktori khusus saat pengujian).
     */
    public function findLocalPdfPath(string $docKey, ?string $pdfDir = null): ?string
    {
        $baseDir = rtrim($pdfDir ?? storage_path('app/private/jdih'), '/\\');
        if (! is_dir($baseDir)) {
            return null;
        }

        $candidates = [
            $baseDir.DIRECTORY_SEPARATOR.$docKey.'.pdf',
            $baseDir.DIRECTORY_SEPARATOR.strtolower($docKey).'.pdf',
            $baseDir.DIRECTORY_SEPARATOR.strtoupper($docKey).'.pdf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Format nomor peraturan baku (mis. "UU No. 24 Tahun 2013").
     *
     * @param  array<string, mixed>  $entry
     */
    public function formatDocumentNumber(array $entry): string
    {
        $jenis = trim((string) ($entry['jenis'] ?? ''));
        $nomor = trim((string) ($entry['nomor'] ?? ''));
        $tahun = (int) ($entry['tahun'] ?? 0);

        if ($nomor === '') {
            return $jenis !== '' ? $jenis : (string) ($entry['doc_key'] ?? '-');
        }

        if ($tahun > 0) {
            return trim(sprintf('%s No. %s Tahun %d', $jenis, $nomor, $tahun));
        }

        return trim(sprintf('%s No. %s', $jenis, $nomor));
    }
}
