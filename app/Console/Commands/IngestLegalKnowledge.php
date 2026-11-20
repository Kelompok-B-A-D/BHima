<?php

namespace App\Console\Commands;

use App\Services\LegalIngestionService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IngestLegalKnowledge extends Command
{
    protected $signature = 'bhima:ingest-knowledge
        {--seed : Muat data dokumen JDIH terverifikasi dan SOP Layanan ke database terlebih dahulu}
        {--document= : Proses ulang satu dokumen JDIH berdasarkan document key, mis. JDIH-001}';

    protected $description = 'Ekstrak teks (PDF/manifest resmi), pecah per pasal/ayat, tandai sanksi, buat embedding, dan indeks secara idempotent';

    public function handle(LegalIngestionService $ingestionService): int
    {
        $documentKey = trim((string) $this->option('document'));
        if ($documentKey !== '') {
            if ($this->option('seed')) {
                $this->info('Memuat korpus dokumen JDIH inti sebelum pemrosesan dokumen terpilih...');
                app(LegalKnowledgeSeeder::class)->run();
            }

            $result = $ingestionService->processDocument($documentKey);
            if ($result['status'] !== 'indexed') {
                $this->error($result['message']);

                return self::FAILURE;
            }

            $this->info($result['message']);

            return self::SUCCESS;
        }

        $existingDocs = DB::table('dashboard_records')
            ->whereIn('collection', ['documents', 'contents'])
            ->count();

        if ($this->option('seed') || $existingDocs === 0) {
            $this->info('Memuat korpus dokumen JDIH inti (tanpa templat karangan) dan SOP layanan ke basis data...');
            app(LegalKnowledgeSeeder::class)->run();
        }

        $this->info('Menjalankan ekstraksi teks, pemecahan pasal/ayat, penandaan pasal sanksi, dan pembuatan embedding...');
        $stats = $ingestionService->ingestAll();

        $this->info(sprintf(
            'Indeksasi selesai: %d dokumen diproses (%d JDIH, %d SOP) menghasilkan %d potongan pasal/ayat (chunks).',
            $stats['documents_processed'],
            $stats['jdih_count'],
            $stats['sop_count'],
            $stats['chunks_indexed']
        ));

        return self::SUCCESS;
    }
}
