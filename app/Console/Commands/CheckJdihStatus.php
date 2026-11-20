<?php

namespace App\Console\Commands;

use App\Services\JdihManifestService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckJdihStatus extends Command
{
    protected $signature = 'bhima:check-jdih-status
        {--sanctions : Tampilkan daftar 100% pasal sanksi terindeks untuk review dua orang}
        {--manifest= : Path berkas jdih_manifest.csv khusus}';

    protected $description = 'Audit bulanan status peraturan JDIH (berlaku/diubah/dicabut), ketersediaan PDF resmi, dan review pasal sanksi';

    public function handle(JdihManifestService $manifestService): int
    {
        $customManifest = $this->option('manifest') ? (string) $this->option('manifest') : null;
        $manifestPath = $manifestService->resolveManifestPath($customManifest);
        $entries = $manifestService->loadAll($customManifest);

        if (empty($entries)) {
            $this->error('Manifest JDIH tidak ditemukan atau kosong.');

            return self::FAILURE;
        }

        $this->info('Manifest JDIH aktif: '.$manifestPath);

        $counts = ['berlaku' => 0, 'diubah' => 0, 'dicabut' => 0];
        $pdfAvailable = 0;
        $staleOrUnverified = [];
        $cutoffDate = Carbon::now()->subDays(30);

        foreach ($entries as $docKey => $entry) {
            $status = $entry['status'] ?? 'berlaku';
            $counts[$status] = ($counts[$status] ?? 0) + 1;

            $hasPdf = $manifestService->findLocalPdfPath($docKey) !== null;
            if ($hasPdf) {
                $pdfAvailable++;
            }

            $verDate = trim((string) ($entry['tanggal_verifikasi'] ?? ''));
            $isStale = true;
            if ($verDate !== '') {
                try {
                    $isStale = Carbon::parse($verDate)->lt($cutoffDate);
                } catch (\Throwable) {
                    $isStale = true;
                }
            }

            $verifikator = strtolower((string) ($entry['verifikator'] ?? ''));
            $needsHumanReview = $verifikator === ''
                || str_contains($verifikator, 'perlu')
                || str_contains($verifikator, 'menunggu')
                || str_contains($verifikator, 'belum');

            if ($isStale || $needsHumanReview || ! $hasPdf) {
                $staleOrUnverified[] = [
                    $docKey,
                    $manifestService->formatDocumentNumber($entry),
                    $status,
                    $hasPdf ? 'Ada' : 'Belum diunduh',
                    $verDate !== '' ? $verDate : '-',
                    $entry['verifikator'] !== '' ? $entry['verifikator'] : '-',
                ];
            }
        }

        $this->line(sprintf(
            'Total entri manifest: %d (Berlaku: %d, Diubah: %d, Dicabut: %d) | PDF resmi lokal: %d/%d',
            count($entries),
            $counts['berlaku'],
            $counts['diubah'],
            $counts['dicabut'],
            $pdfAvailable,
            count($entries)
        ));

        if (! empty($staleOrUnverified)) {
            $this->warn(sprintf('%d dokumen perlu pembaruan PDF / review dua orang / pengecekan bulanan:', count($staleOrUnverified)));
            $this->table(
                ['Doc Key', 'Nomor', 'Status', 'PDF Lokal', 'Tgl Cek', 'Verifikator'],
                array_slice($staleOrUnverified, 0, 15)
            );
        }

        try {
            if (Schema::hasTable('knowledge_chunks') && Schema::hasColumn('knowledge_chunks', 'status_berlaku')) {
                $activeRevokedLeak = DB::table('knowledge_chunks')
                    ->where('status_berlaku', 'dicabut')
                    ->distinct('document_key')
                    ->pluck('document_key')
                    ->all();

                $this->line('Dokumen arsip berstatus dicabut di knowledge_chunks (dikecualikan dari rujukan utama): '.(empty($activeRevokedLeak) ? '0' : implode(', ', $activeRevokedLeak)));

                if ($this->option('sanctions')) {
                    $sanctionChunks = DB::table('knowledge_chunks')
                        ->where('is_ketentuan_pidana', true)
                        ->where('status_berlaku', '!=', 'dicabut')
                        ->orderBy('document_key')
                        ->orderBy('chunk_index')
                        ->get([
                            'document_key',
                            'document_number',
                            'article_label',
                            'sanksi_jenis',
                            'rujuk_pasal',
                            'status_berlaku',
                            'sanksi_teks',
                        ]);

                    $this->info(sprintf('Daftar Pasal Sanksi Aktif untuk Review Dua Orang (%d pasal):', count($sanctionChunks)));
                    $this->table(
                        ['Doc Key', 'Nomor', 'Pasal', 'Jenis', 'Rujuk Pasal', 'Status', 'Bunyi Sanksi'],
                        $sanctionChunks->map(fn ($r) => [
                            $r->document_key,
                            $r->document_number,
                            $r->article_label,
                            $r->sanksi_jenis ?? '-',
                            $r->rujuk_pasal ?? '-',
                            $r->status_berlaku ?? 'berlaku',
                            mb_strimwidth((string) ($r->sanksi_teks ?? ''), 0, 80, '...'),
                        ])->all()
                    );
                }
            }
        } catch (\Throwable $e) {
            $this->warn('Audit database knowledge_chunks dilewati karena koneksi database tidak tersedia: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
