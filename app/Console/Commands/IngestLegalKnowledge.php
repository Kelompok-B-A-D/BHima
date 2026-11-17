<?php

namespace App\Console\Commands;

use App\Services\LegalIngestionService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IngestLegalKnowledge extends Command
{
    protected $signature = 'bhima:ingest-knowledge {--seed : Muat data 100 dokumen JDIH dan SOP Layanan ke database terlebih dahulu}';

    protected $description = 'Ekstrak teks, pecah per pasal/ayat, buat embedding, dan indeks 100 dokumen JDIH & SOP secara idempotent';

    public function handle(LegalIngestionService $ingestionService): int
    {
        $existingDocs = DB::table('dashboard_records')
            ->whereIn('collection', ['documents', 'contents'])
            ->count();

        if ($this->option('seed') || $existingDocs < 100) {
            $this->info('Memuat korpus 100 dokumen JDIH dan SOP layanan ke basis data...');
            app(LegalKnowledgeSeeder::class)->run();
        }

        $this->info('Menjalankan ekstraksi teks, pemecahan pasal/ayat, dan pembuatan embedding...');
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

