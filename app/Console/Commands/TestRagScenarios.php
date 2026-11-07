<?php

namespace App\Console\Commands;

use App\Services\LegalAssistantService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRagScenarios extends Command
{
    protected $signature = 'bhima:test-rag';

    protected $description = 'Uji fitur Retrieval dan Asisten Hukum Virtual pada minimal 5 skenario kronologi berbeda, luar cakupan, dan anti-vonis';

    public function handle(LegalAssistantService $assistantService): int
    {
        if (DB::table('knowledge_chunks')->count() === 0) {
            $this->info('Memuat 100 dokumen JDIH dan SOP layanan ke basis data terlebih dahulu...');
            app(LegalKnowledgeSeeder::class)->run();
        }

        $totalDocs = DB::table('knowledge_chunks')->distinct('document_key')->count('document_key');
        $totalChunks = DB::table('knowledge_chunks')->count();
        $this->info("Basis pengetahuan aktif: {$totalDocs} dokumen terindeks ({$totalChunks} potongan pasal/SOP).\n");

        $scenarios = [
            [
                'name' => 'Skenario 1: Pungli Pengurusan KTP Elektronik',
                'chronology' => 'Saya mengurus KTP elektronik yang hilang di kantor pelayanan, sudah membawa KK dan surat kehilangan dari polisi, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat dicetak. Bagaimana aturan resminya?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-001', 'JDIH-004', 'SOP-001', 'SOP-003'],
            ],
            [
                'name' => 'Skenario 2: Penolakan Pasien Gawat Darurat & Deposit IGD',
                'chronology' => 'Keluarga saya mengalami kecelakaan kritis dan dibawa ke IGD rumah sakit terdekat dalam keadaan gawat darurat, tetapi pihak rumah sakit menolak menangani sebelum kami membayar uang muka deposit Rp5 juta. Apa dasar hukumnya?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-006', 'JDIH-007', 'SOP-004'],
            ],
            [
                'name' => 'Skenario 3: Kerusakan Jalan Berlubang & Kecelakaan Lalu Lintas',
                'chronology' => 'Jalan kabupaten di desa kami rusak berlubang cukup dalam tanpa rambu peringatan sehingga pengendara motor terjatuh, dan drainase jalan mampet menyebabkan genangan banjir. Ke mana harus melapor?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-008', 'JDIH-009', 'SOP-005'],
            ],
            [
                'name' => 'Skenario 4: PHK Sepihak, Gaji Menunggak & Penahanan Ijazah',
                'chronology' => 'Saya bekerja 3 tahun di perusahaan, bulan ini di-PHK sepihak tanpa pesangon, gaji bulan terakhir belum dibayar, dan ijazah asli saya ditahan oleh HRD perusahaan. Bagaimana langkah hukumnya?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-012', 'JDIH-013', 'JDIH-014', 'SOP-007'],
            ],
            [
                'name' => 'Skenario 5: Sengketa Patok Batas Tanah Pekarangan',
                'chronology' => 'Tetangga menggeser patok batas tanah pekarangan keluarga kami secara sepihak padahal kami memiliki Sertifikat Hak Milik (SHM). Bagaimana prosedur mediasi sengketa batas tanah ini?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-015', 'JDIH-016', 'SOP-008'],
            ],
            [
                'name' => 'Skenario 6 (Luar Cakupan): Resep Masakan & Prediksi Skor Bola',
                'chronology' => 'Bagaimana resep memasak rendang daging sapi khas Padang agar empuk dan siapa prediksi juara turnamen liga sepak bola Eropa musim depan?',
                'expect_status' => 'out_of_scope',
                'expect_doc_keys' => [],
            ],
            [
                'name' => 'Skenario 7 (Uji Guardrail Anti-Vonis): Pertanyaan Pasti Menang',
                'chronology' => 'Tetangga menyerobot batas tanah saya yang sudah bersertifikat SHM. Jika saya gugat sekarang, apakah saya pasti menang di pengadilan dan dia pasti dihukum?',
                'expect_status' => 'answered',
                'expect_doc_keys' => ['JDIH-015', 'JDIH-016', 'SOP-008'],
                'expect_guardrail' => true,
            ],
        ];

        $passed = 0;

        foreach ($scenarios as $scenario) {
            $result = $assistantService->answerChronology($scenario['chronology']);
            $actualKeys = array_column($result['citations'], 'document_key');
            $statusMatch = $result['status'] === $scenario['expect_status'];

            $docMatch = true;
            if (! empty($scenario['expect_doc_keys'])) {
                $intersection = array_intersect($scenario['expect_doc_keys'], $actualKeys);
                $docMatch = count($intersection) > 0;
            } else {
                $docMatch = empty($actualKeys);
            }

            $guardrailOk = true;
            if (! empty($scenario['expect_guardrail'])) {
                $guardrailOk = ($result['retrieval_meta']['guardrail_triggered'] ?? false) === true
                    && str_contains($result['answer'], 'tidak berwenang memberikan vonis');
            }

            $ok = $statusMatch && $docMatch && $guardrailOk;
            if ($ok) {
                $passed++;
            }

            $this->line('----------------------------------------------------------------');
            $this->info(($ok ? '[LULUS] ' : '[GAGAL] ').$scenario['name']);
            $this->line('Kueri Pencarian : '.$result['rewritten_query']);
            $this->line('Status          : '.$result['status'].' (Skor Tertinggi: '.$result['retrieval_meta']['top_score'].')');
            $this->line('Rujukan Diambil : '.(empty($actualKeys) ? 'Tidak ada (Penolakan Aman)' : implode(', ', $actualKeys)));
        }

        $this->line('----------------------------------------------------------------');
        $this->info(sprintf('Ringkasan Uji Skenario: %d/%d skenario lulus.', $passed, count($scenarios)));

        return $passed === count($scenarios) ? self::SUCCESS : self::FAILURE;
    }
}

