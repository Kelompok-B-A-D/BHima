<?php

namespace Tests\Feature;

use App\Services\LegalAnalysisService;
use App\Services\LegalAssistantService;
use App\Services\LegalIngestionService;
use Database\Seeders\LegalKnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class JdihAccuracyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bagian 9 Panduan JDIH BHIMA:
     * Uji akurasi >= 50 pertanyaan mencakup seluruh topik, penarikan otomatis pasal sanksi via rujuk_pasal,
     * pengecualian dokumen berstatus dicabut, catatan dokumen berstatus diubah, penolakan aman luar cakupan,
     * dan guardrail anti-vonis.
     */
    public function test_fifty_plus_accuracy_questions_pass_all_jdih_verification_criteria(): void
    {
        $this->seed(LegalKnowledgeSeeder::class);

        // Pastikan nol dokumen templat karangan JDIH-026..080 di database
        $this->assertSame(0, DB::table('knowledge_chunks')->where('document_name', 'like', '%Seri %')->count());

        $fixturePath = base_path('tests/fixtures/akurasi_sanksi.json');
        $this->assertFileExists($fixturePath);

        $cases = json_decode((string) file_get_contents($fixturePath), true);
        $this->assertIsArray($cases);
        $this->assertGreaterThanOrEqual(50, count($cases), 'Set uji akurasi minimal harus memuat 50 pertanyaan.');

        $assistant = app(LegalAssistantService::class);
        $analysisService = app(LegalAnalysisService::class);
        $validChunkIndex = DB::table('knowledge_chunks')->get()->keyBy('id');

        foreach ($cases as $case) {
            $id = (string) $case['id'];
            $question = (string) $case['pertanyaan'];
            $expectedStatus = (string) $case['status_diharapkan'];

            $result = $assistant->answerChronology($question);
            $analysis = $analysisService->analyze($question, $result['citations']);

            // 1. Status answered / out_of_scope tepat
            $this->assertSame(
                $expectedStatus,
                $result['status'],
                "[{$id}] Status meleset untuk pertanyaan: {$question} (top_score={$result['retrieval_meta']['top_score']})"
            );

            // 2. Tidak pernah memuat klaim vonis / pasti menang
            $this->assertStringNotContainsStringIgnoringCase('Anda pasti menang', $result['answer'], "[{$id}] Mengandung prediksi vonis.");
            $this->assertStringNotContainsStringIgnoringCase('dijamin menang', $result['answer'], "[{$id}] Mengandung prediksi vonis.");

            // 3. Tidak boleh ada dokumen berstatus dicabut pada rujukan utama maupun analisis
            foreach ($result['citations'] as $cit) {
                $this->assertNotSame(
                    'dicabut',
                    strtolower((string) ($cit['status_berlaku'] ?? 'berlaku')),
                    "[{$id}] Dokumen berstatus dicabut ({$cit['document_key']}) tidak boleh muncul sebagai rujukan utama."
                );
                $this->assertTrue(
                    isset($validChunkIndex[$cit['chunk_id']]),
                    "[{$id}] Sitasi chunk_id={$cit['chunk_id']} tidak ditemukan di indeks knowledge_chunks."
                );
            }

            if (! empty($case['dilarang_dokumen_kunci'])) {
                $returnedDocKeys = array_column($result['citations'], 'document_key');
                foreach ($case['dilarang_dokumen_kunci'] as $forbiddenKey) {
                    $this->assertNotContains(
                        $forbiddenKey,
                        $returnedDocKeys,
                        "[{$id}] Dokumen {$forbiddenKey} berstatus dicabut tetapi masih muncul di hasil."
                    );
                }
            }

            if (! empty($case['wajib_catatan_arsip_dicabut'])) {
                $this->assertStringContainsString(
                    (string) $case['wajib_catatan_arsip_dicabut'],
                    $result['answer'],
                    "[{$id}] Jawaban harus menjelaskan pengganti aturan yang dicabut."
                );
            }

            if ($expectedStatus === 'out_of_scope') {
                $this->assertEmpty($result['citations'], "[{$id}] Kasus out_of_scope tidak boleh punya sitasi.");
                $this->assertTrue($analysis['perlu_eskalasi'], "[{$id}] Kasus out_of_scope harus mengaktifkan tombol eskalasi.");
                $this->assertStringContainsString('belum menemukan', $result['answer'], "[{$id}] Pesan penolakan aman tidak tampil.");

                continue;
            }

            $returnedDocKeys = array_column($result['citations'], 'document_key');
            $returnedArticles = array_column($result['citations'], 'article_label');

            if (! empty($case['dokumen_kunci_salah_satu'])) {
                $this->assertNotEmpty(
                    array_intersect($case['dokumen_kunci_salah_satu'], $returnedDocKeys),
                    "[{$id}] Dokumen yang diharapkan (".implode(', ', $case['dokumen_kunci_salah_satu']).') tidak muncul. Kembali: '.implode(', ', $returnedDocKeys)
                );
            }

            if (! empty($case['pasal_wajib_muncul'])) {
                foreach ($case['pasal_wajib_muncul'] as $requiredArticle) {
                    $this->assertContains(
                        $requiredArticle,
                        $returnedArticles,
                        "[{$id}] Pasal wajib '{$requiredArticle}' tidak muncul (rujuk_pasal / sanksi). Kembali: ".implode(', ', $returnedArticles)
                    );
                }
            }

            if (! empty($case['pasal_diharapkan_salah_satu'])) {
                $this->assertNotEmpty(
                    array_intersect($case['pasal_diharapkan_salah_satu'], $returnedArticles),
                    "[{$id}] Salah satu pasal diharapkan (".implode(', ', $case['pasal_diharapkan_salah_satu']).') tidak muncul. Kembali: '.implode(', ', $returnedArticles)
                );
            }

            // 4. Verifikasi angka/bunyi sanksi 100% identik dengan teks pasal pada chunk
            if (! empty($case['sanksi_harus_memuat'])) {
                $allSanctionTexts = implode(' | ', array_column($analysis['ancaman_sanksi'], 'bunyi_sanksi_ringkas'));
                $this->assertStringContainsStringIgnoringCase(
                    (string) $case['sanksi_harus_memuat'],
                    $allSanctionTexts,
                    "[{$id}] Sanksi diharapkan memuat '{$case['sanksi_harus_memuat']}', aktual: {$allSanctionTexts}"
                );
            }

            foreach ($analysis['ancaman_sanksi'] as $sanksiItem) {
                $matchedChunk = collect($result['citations'])->first(
                    fn (array $c) => $c['article_label'] === $sanksiItem['pasal'] && $c['document_name'] === $sanksiItem['dokumen']
                );
                $this->assertNotNull($matchedChunk, "[{$id}] Entri sanksi tidak cocok dengan chunk hasil retrieval.");
                if ($sanksiItem['bunyi_sanksi_ringkas'] !== 'Tidak tercantum dalam potongan pasal yang ditemukan') {
                    $this->assertStringContainsStringIgnoringCase(
                        $sanksiItem['bunyi_sanksi_ringkas'],
                        (string) $matchedChunk['excerpt'],
                        "[{$id}] Teks sanksi tidak identik dengan naskah pasal pada chunk."
                    );
                }
            }

            if (! empty($case['wajib_guardrail_vonis'])) {
                $this->assertTrue($result['retrieval_meta']['guardrail_triggered'], "[{$id}] Guardrail anti-vonis harus aktif.");
                $this->assertStringContainsString('tidak berwenang memberikan vonis', $result['answer']);
            }
        }
    }

    /**
     * Bagian 5, 6, dan 8 Panduan JDIH BHIMA:
     * Pastikan LegalIngestionService dapat membaca manifest CSV dan berkas PDF resmi di storage/app/private/jdih/,
     * mengekstrak pasal, mengisi pasal_nomor, ayat, is_ketentuan_pidana, sanksi_teks, rujuk_pasal, status_berlaku,
     * source_page_url, dan pdf_page, serta perintah audit bulanan bhima:check-jdih-status berjalan sukses.
     */
    public function test_manifest_and_pdf_ingestion_populates_verification_columns_and_status_command_works(): void
    {
        $tmpDir = storage_path('framework/testing/jdih_pdf_test');
        File::deleteDirectory($tmpDir);
        File::ensureDirectoryExists($tmpDir);

        try {
            $manifestFile = $tmpDir.DIRECTORY_SEPARATOR.'jdih_manifest.csv';
            $csv = implode("\n", [
                'doc_key,jenis,nomor,tahun,judul,kategori,status,diubah_oleh,dicabut_oleh,url_halaman,url_pdf,tanggal_verifikasi,verifikator',
                'JDIH-TEST-01,UU,99,2026,Undang-Undang Uji Ketentuan Sanksi Resmi,Pelayanan Publik,diubah,UU No. 1 Tahun 2026,,https://peraturan.bpk.go.id/Details/337869/uu-no-1-tahun-2026,,2026-11-19,Reviewer1 & Reviewer2',
            ]);
            file_put_contents($manifestFile, $csv);

            // Bangun berkas PDF 2 halaman sederhana dengan operator Tj yang dapat diekstrak per halaman
            $pdfBytes = "%PDF-1.4\n"
                ."1 0 obj << /Length 120 >> stream\n"
                ."BT (Pasal 10 Ayat (1)) Tj (Setiap petugas dilarang memungut biaya tidak resmi atas layanan surat keterangan warga.) Tj ET\n"
                ."endstream endobj\n"
                ."2 0 obj << /Length 160 >> stream\n"
                ."BT (Pasal 88) Tj (Setiap orang yang melanggar ketentuan sebagaimana dimaksud dalam Pasal 10 ayat (1) dipidana dengan pidana penjara paling lama 3 tahun dan denda paling banyak Rp50.000.000,00.) Tj ET\n"
                ."endstream endobj\n"
                ."%%EOF";
            file_put_contents($tmpDir.DIRECTORY_SEPARATOR.'JDIH-TEST-01.pdf', $pdfBytes);

            $ingestion = app(LegalIngestionService::class);
            $stats = $ingestion->ingestAll($manifestFile, $tmpDir);

            $this->assertSame(1, $stats['documents_processed']);
            $this->assertSame(2, $stats['chunks_indexed']);

            $chunks = DB::table('knowledge_chunks')
                ->where('document_key', 'JDIH-TEST-01')
                ->orderBy('chunk_index')
                ->get();

            $this->assertCount(2, $chunks);

            // Halaman 1: Pasal 10 Ayat (1) (pasal norma)
            $this->assertSame('10', $chunks[0]->pasal_nomor);
            $this->assertSame('1', $chunks[0]->ayat);
            $this->assertFalse((bool) $chunks[0]->is_ketentuan_pidana);
            $this->assertSame('diubah', $chunks[0]->status_berlaku);
            $this->assertSame('UU No. 1 Tahun 2026', $chunks[0]->diubah_oleh);
            $this->assertSame('https://peraturan.bpk.go.id/Details/337869/uu-no-1-tahun-2026', $chunks[0]->source_page_url);
            $this->assertSame(1, (int) $chunks[0]->pdf_page);

            // Halaman 2: Pasal 88 (pasal sanksi merujuk Pasal 10 ayat (1))
            $this->assertSame('88', $chunks[1]->pasal_nomor);
            $this->assertTrue((bool) $chunks[1]->is_ketentuan_pidana);
            $this->assertSame('pidana', $chunks[1]->sanksi_jenis);
            $this->assertStringContainsString('pidana penjara paling lama 3 tahun', (string) $chunks[1]->sanksi_teks);
            $this->assertStringContainsString('Pasal 10', (string) $chunks[1]->rujuk_pasal);
            $this->assertSame(2, (int) $chunks[1]->pdf_page);

            // Uji command audit bulanan + daftar pasal sanksi
            $this->artisan('bhima:check-jdih-status', [
                '--sanctions' => true,
                '--manifest' => $manifestFile,
            ])->assertExitCode(0);
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    /**
     * Verifikasi perbaikan seluruh 40 skenario pengujian dari laporan_kesalahan_asisten_hukum.md (T01 s.d. T40):
     * memastikan tidak ada kebocoran UU Kesehatan IGD ke kasus non-medis (BUG-02), tidak ada polusi pasal pidana lintas topik (BUG-03),
     * tidak ada over-stemming pelaku -> pela (BUG-04), tidak ada kebocoran UU KDRT di luar rumah tangga (BUG-05),
     * tidak ada pencocokan kata tunggal umum lintas domain (BUG-06), slang/bahasa awam warga dikenali (BUG-07),
     * serta sub-topik dan pasal pidana sektoral sesuai (BUG-08 & BUG-09).
     */
    public function test_forty_adversarial_citizen_chronologies_from_error_report_produce_accurate_outputs(): void
    {
        $this->seed(LegalKnowledgeSeeder::class);
        $assistant = app(LegalAssistantService::class);

        $cases = [
            ['id' => 'T01', 'q' => 'Saya ditipu saat belanja HP di marketplace online, uang sudah ditransfer tapi barang tidak dikirim dan penjual memblokir nomor saya.', 'status' => 'answered', 'must_have_docs' => ['JDIH-021'], 'must_have_articles' => ['Pasal 16'], 'forbidden_docs' => ['JDIH-006', 'SOP-004', 'SOP-009', 'JDIH-017']],
            ['id' => 'T02', 'q' => 'Teman saya pinjam uang Rp10 juta pakai perjanjian tertulis tapi sudah 1 tahun tidak mau bayar dan menghilang.', 'status' => 'out_of_scope'],
            ['id' => 'T03', 'q' => 'Saya dipukul dan dikeroyok tetangga sampai luka memar karena masalah parkir mobil di depan rumah.', 'status' => 'out_of_scope'],
            ['id' => 'T04', 'q' => 'Pacar saya melakukan kekerasan fisik dan mengancam menyebarkan foto pribadi saya kalau saya minta putus.', 'status' => 'answered', 'must_have_docs' => ['JDIH-017', 'SOP-010'], 'forbidden_docs' => ['JDIH-019']],
            ['id' => 'T05', 'q' => 'Saya mengalami pelecehan seksual oleh atasan laki-laki di kantor tempat saya bekerja.', 'status' => 'answered', 'must_have_docs' => ['SOP-010', 'JDIH-014'], 'must_have_articles' => ['Pasal 28 Ayat (1)'], 'forbidden_docs' => ['JDIH-019']],
            ['id' => 'T06', 'q' => 'Motor saya dicuri maling saat diparkir di halaman masjid tadi malam, pasal apa untuk pelaku pencurian?', 'status' => 'out_of_scope'],
            ['id' => 'T07', 'q' => 'Saya ditabrak pengendara motor yang menerobos lampu merah lalu pelaku kabur tabrak lari, pasal berapa untuk pelaku?', 'status' => 'answered', 'must_have_docs' => ['JDIH-008'], 'must_have_articles' => ['Pasal 312', 'Pasal 231 Ayat (1)'], 'forbidden_docs' => ['JDIH-021', 'SOP-005']],
            ['id' => 'T08', 'q' => 'Anak saya dibully dan dipukul teman sekelasnya di sekolah SMP sampai trauma tidak mau masuk sekolah.', 'status' => 'answered', 'must_have_docs' => ['SOP-010'], 'forbidden_docs' => ['JDIH-019']],
            ['id' => 'T09', 'q' => 'Akun Instagram dan WhatsApp saya diretas orang tidak dikenal lalu dipakai menipu teman-teman saya.', 'status' => 'out_of_scope'],
            ['id' => 'T10', 'q' => 'Tetangga saya memfitnah dan mencemarkan nama baik saya di grup WhatsApp RT dengan tuduhan palsu.', 'status' => 'out_of_scope'],
            ['id' => 'T11', 'q' => 'Suami saya selingkuh dan saya ingin mengajukan gugatan cerai serta hak asuh anak di pengadilan agama.', 'status' => 'out_of_scope'],
            ['id' => 'T12', 'q' => 'Pemilik kontrakan mengusir saya secara sepihak sebelum masa sewa 1 tahun habis dan tidak mengembalikan uang sewa.', 'status' => 'out_of_scope'],
            ['id' => 'T13', 'q' => 'Dokter di rumah sakit salah memberikan obat saat rawat jalan sehingga saya alergi parah dugaan malapraktik medis.', 'status' => 'answered', 'must_have_docs' => ['JDIH-006'], 'must_have_articles' => ['Pasal 193', 'Pasal 305 Ayat (1)'], 'forbidden_docs' => ['SOP-004', 'SOP-014', 'JDIH-007'], 'forbidden_articles' => ['Pasal 174 Ayat (1)', 'Pasal 174 Ayat (2)', 'Pasal 438 Ayat (1)']],
            ['id' => 'T14', 'q' => 'Kartu BPJS Kesehatan mandiri saya menunggak iuran 6 bulan sehingga tidak bisa dipakai berobat ke puskesmas.', 'status' => 'answered', 'must_have_docs' => ['JDIH-007'], 'must_have_articles' => ['Pasal 42 Ayat (1)', 'Pasal 42 Ayat (3)'], 'forbidden_docs' => ['SOP-014'], 'forbidden_articles' => ['Pasal 8 Ayat (1)']],
            ['id' => 'T15', 'q' => 'Kakak saya menguasai seluruh rumah warisan orang tua dan tidak mau membagi warisan kepada adik-adiknya.', 'status' => 'answered', 'must_have_docs' => ['SOP-018'], 'must_have_articles' => ['Pasal 2 Ayat (1)']],
            ['id' => 'T16', 'q' => 'Saya membeli tanah kavling dari developer tapi sertifikat SHM tidak kunjung diterbitkan setelah lunas 3 tahun.', 'status' => 'answered', 'must_have_docs' => ['JDIH-015', 'JDIH-021'], 'must_have_articles' => ['Pasal 37 Ayat (1)', 'Pasal 16'], 'forbidden_docs' => ['SOP-008'], 'forbidden_articles' => ['Pasal 57 Ayat (1)']],
            ['id' => 'T17', 'q' => 'Saya bekerja lembur setiap hari sampai malam tapi perusahaan tidak pernah membayar uang lembur dan THR lebaran saya.', 'status' => 'answered', 'must_have_docs' => ['JDIH-012', 'JDIH-013', 'SOP-007'], 'must_have_articles' => ['Pasal 31 Ayat (1)', 'Pasal 9 Ayat (1)', 'Pasal 78 Ayat (1)'], 'forbidden_articles' => ['Pasal 40 Ayat (1)']],
            ['id' => 'T18', 'q' => 'Majikan tempat saya bekerja sebagai asisten rumah tangga ART tidak membayar gaji saya 3 bulan dan melarang saya keluar rumah.', 'status' => 'answered', 'must_have_docs' => ['JDIH-019', 'SOP-010', 'SOP-007'], 'must_have_articles' => ['Pasal 2 Ayat (1)', 'Pasal 9 Ayat (1)']],
            ['id' => 'T19', 'q' => 'Ketua RT memotong uang bantuan sosial BLT dari pemerintah sebesar Rp100.000 per warga saat pembagian.', 'status' => 'answered', 'must_have_docs' => ['JDIH-005', 'SOP-003'], 'must_have_articles' => ['Pasal 12 huruf e'], 'forbidden_docs' => ['SOP-004', 'SOP-006', 'SOP-013']],
            ['id' => 'T20', 'q' => 'Tetangga membangun rumah melebihi batas dan air talang hujannya jatuh ke halaman rumah saya.', 'status' => 'answered', 'must_have_docs' => ['SOP-008', 'JDIH-015', 'JDIH-016'], 'forbidden_docs' => ['SOP-010', 'SOP-001', 'JDIH-019']],
            ['id' => 'T21', 'q' => 'ktp saya ilang trus dimintain duit 50rb ama org kelurahan', 'status' => 'answered', 'must_have_docs' => ['JDIH-001', 'SOP-003'], 'must_have_articles' => ['Pasal 79A', 'Pasal 95B'], 'forbidden_docs' => ['SOP-008']],
            ['id' => 'T22', 'q' => 'bos saya ga bayar gaji 2 bulan terus ijazah ditahan ga dikasih', 'status' => 'answered', 'must_have_docs' => ['SOP-007', 'JDIH-013', 'JDIH-014'], 'forbidden_articles' => ['Pasal 15 Ayat (1)']],
            ['id' => 'T23', 'q' => 'orang tua saya serangan jantung dibawa ke ugd tapi disuruh bayar dp dulu baru ditangani', 'status' => 'answered', 'must_have_docs' => ['JDIH-006', 'SOP-004'], 'must_have_articles' => ['Pasal 174 Ayat (2)', 'Pasal 438 Ayat (1)']],
            ['id' => 'T24', 'q' => 'pinjol sebar foto ktp saya ke grup wa kantor saya, ancaman hukumannya berapa tahun?', 'status' => 'answered', 'must_have_docs' => ['SOP-009', 'JDIH-017'], 'must_have_articles' => ['Pasal 65 Ayat (2)', 'Pasal 67 Ayat (2)']],
            ['id' => 'T25', 'q' => 'jalan depan rumah bolong gede bikin orang jatoh motor, dinas bisa dipidana gak?', 'status' => 'answered', 'must_have_docs' => ['JDIH-008', 'SOP-005'], 'must_have_articles' => ['Pasal 24 Ayat (1)', 'Pasal 273 Ayat (1)']],
            ['id' => 'T26', 'q' => 'saya mau minta laporan penggunaan dana desa ke kades tapi ditolak terus', 'status' => 'answered', 'must_have_docs' => ['JDIH-020', 'JDIH-022', 'SOP-011']],
            ['id' => 'T27', 'q' => 'suami saya sering kasar mukulin saya dan anak, saya takut lapor', 'status' => 'answered', 'must_have_docs' => ['SOP-010', 'JDIH-019']],
            ['id' => 'T28', 'q' => 'saya orang ga mampu mau cari pengacara gratis untuk kasus pidana di pengadilan', 'status' => 'answered', 'must_have_docs' => ['SOP-006', 'SOP-013'], 'forbidden_docs' => ['JDIH-025']],
            ['id' => 'T29', 'q' => 'Petugas Disdukcapil minta uang untuk bikin KTP-el, pasal apa dan sanksinya?', 'status' => 'answered', 'must_have_docs' => ['JDIH-001', 'SOP-001'], 'must_have_articles' => ['Pasal 79A', 'Pasal 95B']],
            ['id' => 'T30', 'q' => 'Kalau kepala desa korupsi dana desa, pasal apa yang dikenakan dan berapa tahun penjaranya?', 'status' => 'answered', 'must_have_docs' => ['JDIH-005', 'JDIH-022'], 'must_have_articles' => ['Pasal 3']],
            ['id' => 'T31', 'q' => 'Perusahaan membayar gaji karyawan di bawah UMR UMK daerah, apa ancaman pidananya?', 'status' => 'answered', 'must_have_docs' => ['JDIH-013', 'SOP-007'], 'must_have_articles' => ['Pasal 23 Ayat (3)', 'Pasal 79 Ayat (1)'], 'forbidden_docs' => ['JDIH-005', 'JDIH-012']],
            ['id' => 'T32', 'q' => 'Pabrik membuang limbah beracun ke sungai dekat pemukiman warga, berapa tahun ancaman penjara dan dendanya?', 'status' => 'answered', 'must_have_docs' => ['JDIH-023', 'SOP-015'], 'must_have_articles' => ['Pasal 69 Ayat (1)', 'Pasal 98 Ayat (1)', 'Pasal 104'], 'forbidden_docs' => ['JDIH-005', 'JDIH-017', 'JDIH-008']],
            ['id' => 'T33', 'q' => 'Oknum petugas kecamatan meminta pungli saat pengurusan surat pindah domisili, apakah masih bisa dilaporkan ke Satgas Saber Pungli berdasarkan Perpres 87 Tahun 2016?', 'status' => 'answered', 'must_have_docs' => ['JDIH-001', 'JDIH-002'], 'forbidden_docs' => ['JDIH-004']],
            ['id' => 'T34', 'q' => 'Barang yang saya beli di toko elektronik rusak cacat dalam 2 hari tapi toko tidak mau ganti rugi, apa sanksi pidana bagi pemilik toko?', 'status' => 'answered', 'must_have_docs' => ['JDIH-021', 'SOP-016'], 'must_have_articles' => ['Pasal 62 Ayat (1)', 'Pasal 19 Ayat (1)'], 'forbidden_docs' => ['JDIH-017', 'JDIH-008']],
            ['id' => 'T35', 'q' => 'Saya dipecat sepihak dari kerjaan tanpa pesangon, kalau saya gugat ke pengadilan apakah saya pasti menang 100 persen?', 'status' => 'answered', 'must_have_docs' => ['JDIH-012', 'SOP-007'], 'forbidden_docs' => ['JDIH-013']],
            ['id' => 'T36', 'q' => 'Saya mengalami penipuan investasi bodong kripto senilai Rp50 juta oleh kenalan di Telegram.', 'status' => 'out_of_scope'],
            ['id' => 'T37', 'q' => 'Tetangga saya memutar musik keras sekali setiap malam sampai jam 2 pagi sehingga mengganggu istirahat warga.', 'status' => 'out_of_scope'],
            ['id' => 'T38', 'q' => 'Saya kena razia polisi dan ditilang karena lupa membawa SIM dan STNK serta pajak motor mati.', 'status' => 'out_of_scope'],
            ['id' => 'T39', 'q' => 'Bagaimana cara mengurus izin mendirikan bangunan PBG untuk renovasi rumah tingkat 2?', 'status' => 'out_of_scope'],
            ['id' => 'T40', 'q' => 'Seorang oknum mengancam akan membunuh saya lewat pesan WhatsApp karena sengketa bisnis.', 'status' => 'out_of_scope'],
        ];

        foreach ($cases as $c) {
            $res = $assistant->answerChronology($c['q']);
            $this->assertSame($c['status'], $res['status'], "[{$c['id']}] Status tidak sesuai untuk: {$c['q']}");

            if ($c['status'] === 'out_of_scope') {
                $this->assertEmpty($res['citations'], "[{$c['id']}] Kasus out_of_scope tidak boleh memiliki sitasi.");

                continue;
            }

            $docKeys = array_column($res['citations'], 'document_key');
            $articles = array_column($res['citations'], 'article_label');

            foreach ($c['must_have_docs'] ?? [] as $expectedDoc) {
                $this->assertContains($expectedDoc, $docKeys, "[{$c['id']}] Dokumen wajib {$expectedDoc} tidak muncul. Aktual: ".implode(', ', $docKeys));
            }
            foreach ($c['must_have_articles'] ?? [] as $expectedArt) {
                $this->assertContains($expectedArt, $articles, "[{$c['id']}] Pasal wajib {$expectedArt} tidak muncul. Aktual: ".implode(', ', $articles));
            }
            foreach ($c['forbidden_docs'] ?? [] as $forbiddenDoc) {
                $this->assertNotContains($forbiddenDoc, $docKeys, "[{$c['id']}] Dokumen terlarang {$forbiddenDoc} masih bocor ke hasil.");
            }
            foreach ($c['forbidden_articles'] ?? [] as $forbiddenArt) {
                $this->assertNotContains($forbiddenArt, $articles, "[{$c['id']}] Pasal terlarang {$forbiddenArt} masih bocor ke hasil.");
            }
        }
    }
}
