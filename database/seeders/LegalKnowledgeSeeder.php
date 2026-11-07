<?php

namespace Database\Seeders;

use App\Services\LegalIngestionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalKnowledgeSeeder extends Seeder
{
    /**
     * Muat 100 dokumen hukum JDIH (80 dokumen) dan SOP Layanan (20 dokumen) secara idempotent,
     * lalu jalankan proses ekstraksi, pemecahan pasal/ayat, dan indeksasi embedding.
     */
    public function run(): void
    {
        $now = now();
        $today = $now->toDateString();

        $rows = [];
        foreach ($this->buildJdihDocuments($today) as $doc) {
            $rows[] = [
                'collection' => 'documents',
                'record_key' => $doc['id'],
                'payload' => json_encode($doc, JSON_UNESCAPED_UNICODE),
                'file_path' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        foreach ($this->buildSopContents($today) as $sop) {
            $rows[] = [
                'collection' => 'contents',
                'record_key' => $sop['id'],
                'payload' => json_encode($sop, JSON_UNESCAPED_UNICODE),
                'file_path' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 50) as $batch) {
            DB::table('dashboard_records')->upsert(
                $batch,
                ['collection', 'record_key'],
                ['payload', 'file_path', 'updated_at']
            );
        }

        app(LegalIngestionService::class)->ingestAll();
    }

    /**
     * Bangun 80 dokumen JDIH dengan pasal dan ayat lengkap.
     *
     * @return list<array<string, mixed>>
     */
    public function buildJdihDocuments(string $today): array
    {
        $coreDocuments = [
            [
                'id' => 'JDIH-001',
                'title' => 'Undang-Undang Perubahan atas Administrasi Kependudukan',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 24 Tahun 2013',
                'year' => 2013,
                'category' => 'Administrasi Kependudukan',
                'summary' => 'Mengatur pengurusan dan penerbitan dokumen kependudukan seperti KTP-el, Kartu Keluarga, Akta Kelahiran, dan Akta Kematian secara gratis tanpa dipungut biaya serta sanksi pidana bagi petugas yang melakukan pungutan liar.',
                'body' => <<<'TEXT'
Pasal 8 Ayat (1)
Instansi Pelaksana melaksanakan urusan Administrasi Kependudukan dengan kewajiban yang meliputi mendaftar Peristiwa Kependudukan dan mencatat Peristiwa Penting secara profesional, transparan, dan tepat waktu.
Pasal 8 Ayat (2)
Kewajiban sebagaimana dimaksud pada ayat (1) untuk penerbitan dokumen KTP elektronik (KTP-el), Kartu Keluarga (KK), dan Akta Pencatatan Sipil dilaksanakan paling lambat 14 (empat belas) hari kerja sejak tanggal dipenuhinya semua persyaratan, atau lebih cepat sesuai standar pelayanan daerah.
Pasal 79A
Pengurusan dan penerbitan Dokumen Kependudukan tidak dipungut biaya (gratis). Setiap warga negara berhak memperoleh layanan pembuatan KTP-el baru, penggantian KTP-el rusak atau hilang, Kartu Keluarga, Akta Kelahiran, dan Akta Kematian tanpa biaya apa pun.
Pasal 95B
Setiap pejabat dan petugas pada Desa/Kelurahan, Kecamatan, UPT Instansi Pelaksana, dan Instansi Pelaksana yang memerintahkan dan/atau memfasilitasi dan/atau melakukan pungutan biaya kepada Penduduk dalam pengurusan dan penerbitan Dokumen Kependudukan sebagaimana dimaksud dalam Pasal 79A dipidana dengan pidana penjara paling lama 6 (enam) tahun dan/atau denda paling banyak Rp75.000.000,00 (tujuh puluh lima juta rupiah).
TEXT,
            ],
            [
                'id' => 'JDIH-002',
                'title' => 'Peraturan Presiden tentang Persyaratan dan Tata Cara Pendaftaran Penduduk dan Pencatatan Sipil',
                'type' => 'Peraturan Presiden',
                'number' => 'Perpres No. 96 Tahun 2018',
                'year' => 2018,
                'category' => 'Administrasi Kependudukan',
                'summary' => 'Menyederhanakan persyaratan pembuatan KTP-el, KK, dan Akta Kelahiran tanpa memerlukan surat pengantar RT/RW atau kelurahan.',
                'body' => <<<'TEXT'
Pasal 14 Ayat (1)
Penerbitan KTP-el baru bagi Penduduk Warga Negara Indonesia dilakukan setelah memenuhi persyaratan telah berusia 17 (tujuh belas) tahun, sudah kawin, atau pernah kawin, cukup dengan menunjukkan fotokopi Kartu Keluarga tanpa perlu surat pengantar RT, RW, atau Desa/Kelurahan.
Pasal 15 Ayat (1)
Penerbitan KTP-el karena hilang atau rusak bagi Penduduk WNI dilakukan dengan melampirkan surat keterangan hilang dari kepolisian untuk KTP-el hilang, atau menyerahkan KTP-el yang rusak disertai fotokopi Kartu Keluarga.
Pasal 72 Ayat (1)
Dalam pelayanan pendaftaran penduduk dan pencatatan sipil pada Disdukcapil, petugas dilarang mensyaratkan surat pengantar dari RT, RW, dan desa/kelurahan selain yang telah ditetapkan dalam Peraturan Presiden ini.
TEXT,
            ],
            [
                'id' => 'JDIH-003',
                'title' => 'Undang-Undang tentang Pelayanan Publik',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 25 Tahun 2009',
                'year' => 2009,
                'category' => 'Pelayanan Publik',
                'summary' => 'Mengatur hak masyarakat atas pelayanan publik yang berkualitas, standar waktu pelayanan, larangan maladministrasi, dan mekanisme pengaduan masyarakat.',
                'body' => <<<'TEXT'
Pasal 18 Ayat (1)
Masyarakat berhak mengetahui kebenaran isi standar pelayanan, mengawasi pelaksanaan standar pelayanan, mendapat tanggapan terhadap pengaduan yang diajukan, serta mendapat advokasi, perlindungan, dan/atau pemenuhan pelayanan.
Pasal 21
Komponen standar pelayanan sekurang-kurangnya meliputi dasar hukum, persyaratan, sistem, mekanisme dan prosedur, jangka waktu penyelesaian, biaya/tarif resmi, produk pelayanan, dan pengelolaan pengaduan.
Pasal 34
Pelaksana dalam menyelenggarakan pelayanan publik harus berperilaku adil dan tidak diskriminatif, cermat, santun dan ramah, tegas, tidak mempersulit, serta dilarang meminta atau menerima imbalan uang, barang, atau fasilitas di luar ketentuan resmi.
Pasal 40 Ayat (1)
Masyarakat berhak mengadukan penyelenggaraan pelayanan publik kepada penyelenggara, Ombudsman, dan/atau Dewan Perwakilan Rakyat apabila penyelenggara tidak melaksanakan kewajiban atau melanggar standar pelayanan.
Pasal 50 Ayat (1)
Pengaduan sebagaimana dimaksud wajib diterima dan ditindaklanjuti oleh penyelenggara paling lambat 14 (empat belas) hari sejak pengaduan diterima secara lengkap.
TEXT,
            ],
            [
                'id' => 'JDIH-004',
                'title' => 'Peraturan Presiden tentang Satuan Tugas Sapu Bersih Pungutan Liar',
                'type' => 'Peraturan Presiden',
                'number' => 'Perpres No. 87 Tahun 2016',
                'year' => 2016,
                'category' => 'Dugaan Pungutan Liar',
                'summary' => 'Mengatur pemberantasan praktik pungutan liar (pungli) pada layanan publik, perizinan, dan administrasi pemerintahan serta peran laporan masyarakat.',
                'body' => <<<'TEXT'
Pasal 2
Satgas Saber Pungli mempunyai tugas melaksanakan pemberantasan pungutan liar secara efektif dan efisien dengan mengoptimalkan pemanfaatan personil, satuan kerja, dan sarana prasarana pada kementerian/lembaga dan pemerintah daerah.
Pasal 3
Dalam melaksanakan tugas pemberantasan pungutan liar, Satgas Saber Pungli menyelenggarakan fungsi intelijen, pencegahan, penindakan, dan yustisi terhadap setiap permintaan biaya tidak resmi pada layanan masyarakat.
Pasal 12 Ayat (1)
Masyarakat dapat berperan serta dalam pemberantasan pungutan liar dengan memberikan informasi, pengaduan, atau bukti dugaan pungutan liar baik secara langsung maupun melalui sistem informasi pengaduan elektronik pemerintah.
Pasal 12 Ayat (2)
Identitas pelapor dugaan pungutan liar wajib dirahasiakan dan dilindungi oleh penyelenggara layanan pengaduan dari segala bentuk intimidasi atau tindakan balasan.
TEXT,
            ],
            [
                'id' => 'JDIH-005',
                'title' => 'Undang-Undang Pemberantasan Tindak Pidana Korupsi',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 20 Tahun 2001',
                'year' => 2001,
                'category' => 'Dugaan Pungutan Liar',
                'summary' => 'Mengatur tindak pidana korupsi termasuk pemerasan dan pungutan tidak sah oleh pegawai negeri atau penyelenggara negara dalam pelayanan masyarakat.',
                'body' => <<<'TEXT'
Pasal 12 huruf e
Dipidana dengan pidana penjara seumur hidup atau pidana penjara paling singkat 4 (empat) tahun dan paling lama 20 (dua puluh) tahun dan pidana denda paling sedikit Rp200.000.000,00 pegawai negeri atau penyelenggara negara yang dengan maksud menguntungkan diri sendiri atau orang lain secara melawan hukum, atau dengan menyalahgunakan kekuasaannya memaksa seseorang memberikan sesuatu, membayar, atau menerima pembayaran dengan potongan.
Pasal 12B Ayat (1)
Setiap gratifikasi kepada pegawai negeri atau penyelenggara negara dianggap pemberian suap apabila berhubungan dengan jabatannya dan yang berlawanan dengan kewajiban atau tugasnya.
Pasal 41 Ayat (1)
Masyarakat dapat berperan serta membantu upaya pencegahan dan pemberantasan tindak pidana korupsi dengan hak mencari, memperoleh, dan memberikan informasi adanya dugaan telah terjadi tindak pidana korupsi serta memperoleh perlindungan hukum.
TEXT,
            ],
            [
                'id' => 'JDIH-006',
                'title' => 'Undang-Undang tentang Kesehatan',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 17 Tahun 2023',
                'year' => 2023,
                'category' => 'Pelayanan Kesehatan',
                'summary' => 'Mengatur kewajiban Rumah Sakit dan Puskesmas memberikan pertolongan pertama pada pasien gawat darurat serta larangan menolak pasien dan larangan meminta uang muka.',
                'body' => <<<'TEXT'
Pasal 174 Ayat (1)
Dalam keadaan Gawat Darurat, Fasilitas Pelayanan Kesehatan baik pemerintah maupun swasta wajib memberikan Pelayanan Kesehatan bagi penyelamatan nyawa Pasien dan pencegahan kedisabilitasan terlebih dahulu.
Pasal 174 Ayat (2)
Dalam keadaan Gawat Darurat sebagaimana dimaksud pada ayat (1), Fasilitas Pelayanan Kesehatan dilarang menolak Pasien dan/atau meminta uang muka (deposit) maupun jaminan pembayaran terlebih dahulu sebelum tindakan penyelamatan diberikan.
Pasal 276 huruf a
Pasien mempunyai hak memperoleh Pelayanan Kesehatan yang manusiawi, adil, jujur, dan tanpa diskriminasi sesuai dengan standar profesi dan standar prosedur operasional.
Pasal 438 Ayat (1)
Setiap pimpinan Fasilitas Pelayanan Kesehatan dan/atau Tenaga Medis atau Tenaga Kesehatan yang dengan sengaja tidak memberikan pertolongan pertama terhadap Pasien yang dalam keadaan Gawat Darurat sebagaimana dimaksud dalam Pasal 174 dipidana dengan pidana penjara paling lama 2 (dua) tahun atau pidana denda paling banyak Rp200.000.000,00.
TEXT,
            ],
            [
                'id' => 'JDIH-007',
                'title' => 'Peraturan Presiden tentang Jaminan Kesehatan (BPJS Kesehatan)',
                'type' => 'Peraturan Presiden',
                'number' => 'Perpres No. 82 Tahun 2018',
                'year' => 2018,
                'category' => 'Pelayanan Kesehatan',
                'summary' => 'Mengatur hak peserta Jaminan Kesehatan Nasional (JKN/BPJS), Penerima Bantuan Iuran (PBI) bagi fakir miskin, pelayanan gawat darurat tanpa surat rujukan, dan larangan pungutan biaya tambahan.',
                'body' => <<<'TEXT'
Pasal 8 Ayat (1)
Peserta Penerima Bantuan Iuran (PBI) Jaminan Kesehatan meliputi orang yang tergolong fakir miskin dan orang tidak mampu yang iurannya dibayarkan oleh Pemerintah Pusat atau Pemerintah Daerah.
Pasal 47 Ayat (1)
Manfaat pelayanan kesehatan bagi peserta JKN bersifat pelayanan perseorangan berupa pelayanan kesehatan yang mencakup pelayanan promotif, preventif, kuratif, dan rehabilitatif termasuk pelayanan obat dan bahan medis habis pakai.
Pasal 62 Ayat (1)
Peserta yang memerlukan pelayanan Gawat Darurat dapat langsung memperoleh pelayanan di setiap Fasilitas Kesehatan baik yang bekerja sama maupun yang tidak bekerja sama dengan BPJS Kesehatan tanpa memerlukan surat rujukan dari Fasilitas Kesehatan Tingkat Pertama.
Pasal 63 Ayat (1)
Fasilitas Kesehatan yang menyelenggarakan pelayanan kesehatan bagi Peserta Jaminan Kesehatan dilarang menarik biaya tambahan di luar ketentuan peraturan perundang-undangan dari Peserta selama Peserta menempati ruang perawatan sesuai haknya.
TEXT,
            ],
            [
                'id' => 'JDIH-008',
                'title' => 'Undang-Undang tentang Lalu Lintas dan Angkutan Jalan',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 22 Tahun 2009',
                'year' => 2009,
                'category' => 'Infrastruktur',
                'summary' => 'Mengatur kewajiban penyelenggara jalan untuk segera memperbaiki jalan yang rusak, memasang tanda peringatan, dan sanksi apabila kelalaian menyebabkan kecelakaan.',
                'body' => <<<'TEXT'
Pasal 24 Ayat (1)
Penyelenggara Jalan wajib segera dan patut untuk memperbaiki Jalan yang rusak yang dapat mengakibatkan Kecelakaan Lalu Lintas.
Pasal 24 Ayat (2)
Dalam hal belum dapat dilakukan perbaikan Jalan yang rusak sebagaimana dimaksud pada ayat (1), penyelenggara Jalan wajib memberi tanda atau rambu pada Jalan yang rusak untuk mencegah terjadinya Kecelakaan Lalu Lintas.
Pasal 238 Ayat (1)
Pemerintah menyediakan dan/atau memperbaiki pengaturan, sarana, dan Prasarana Lalu Lintas yang menjadi penyebab kecelakaan, termasuk penerangan jalan umum dan rambu keselamatan.
Pasal 273 Ayat (1)
Setiap penyelenggara Jalan yang tidak dengan segera dan patut memperbaiki Jalan yang rusak yang mengakibatkan Kecelakaan Lalu Lintas sebagaimana dimaksud dalam Pasal 24 ayat (1) sehingga menimbulkan korban luka ringan dan/atau kerusakan Kendaraan dan/atau barang dipidana dengan penjara paling lama 6 (enam) bulan atau denda paling banyak Rp12.000.000,00.
TEXT,
            ],
            [
                'id' => 'JDIH-009',
                'title' => 'Undang-Undang tentang Jalan',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 2 Tahun 2022',
                'year' => 2022,
                'category' => 'Infrastruktur',
                'summary' => 'Mengatur wewenang pemerintah daerah atas jalan kabupaten/kota dan jalan desa, pemeliharaan rutin jalan dan drainase jalan, serta peran serta masyarakat dalam pelaporan kerusakan.',
                'body' => <<<'TEXT'
Pasal 16 Ayat (1)
Wewenang Pemerintah Daerah kabupaten/kota dalam penyelenggaraan jalan meliputi penyelenggaraan jalan kabupaten/kota dan pembinaan jalan desa agar memenuhi standar pelayanan minimal dan keselamatan pengguna jalan.
Pasal 30 Ayat (1)
Penyelenggara jalan wajib memprioritaskan pemeliharaan rutin dan berkala pada jalan serta sistem drainase jalan untuk mencegah genangan air, banjir, dan kerusakan konstruksi jalan.
Pasal 62 Ayat (1)
Masyarakat berhak memberikan masukan, informasi, dan laporan mengenai kondisi kerusakan jalan, jembatan, maupun saluran drainase jalan kepada penyelenggara jalan untuk memperoleh penanganan sesuai standar teknis.
TEXT,
            ],
            [
                'id' => 'JDIH-010',
                'title' => 'Undang-Undang tentang Bantuan Hukum',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 16 Tahun 2011',
                'year' => 2011,
                'category' => 'Bantuan Hukum',
                'summary' => 'Mengatur hak warga miskin atau tidak mampu untuk memperoleh bantuan hukum secara cuma-cuma (gratis) dalam perkara perdata, pidana, dan tata usaha negara baik litigasi maupun non-litigasi.',
                'body' => <<<'TEXT'
Pasal 4 Ayat (1)
Bantuan Hukum diberikan kepada Penerima Bantuan Hukum yang menghadapi masalah hukum keperdataan, pidana, dan tata usaha negara baik secara litigasi maupun nonlitigasi.
Pasal 5 Ayat (1)
Penerima Bantuan Hukum meliputi setiap orang atau kelompok orang miskin yang tidak dapat memenuhi hak dasar secara layak dan mandiri.
Pasal 12 huruf a
Penerima Bantuan Hukum berhak mendapatkan Bantuan Hukum hingga masalah hukumnya selesai dan/atau perkaranya telah mempunyai kekuatan hukum tetap, selama yang bersangkutan tidak mencabut surat kuasa.
Pasal 14 Ayat (1)
Untuk memperoleh Bantuan Hukum, Pemohon Bantuan Hukum harus mengajukan permohonan secara tertulis atau lisan yang berisi sekurang-kurangnya identitas Pemohon dan uraian singkat mengenai pokok persoalan yang dimohonkan Bantuan Hukum, serta menyerahkan Surat Keterangan Tidak Mampu (SKTM) dari Lurah/Kepala Desa.
Pasal 20
Pemberi Bantuan Hukum dilarang menerima atau meminta pembayaran dari Penerima Bantuan Hukum dan/atau pihak lain yang terkait dengan perkara yang sedang ditangani.
TEXT,
            ],
            [
                'id' => 'JDIH-011',
                'title' => 'Peraturan Pemerintah tentang Syarat dan Tata Cara Pemberian Bantuan Hukum',
                'type' => 'Peraturan Pemerintah',
                'number' => 'PP No. 42 Tahun 2013',
                'year' => 2013,
                'category' => 'Bantuan Hukum',
                'summary' => 'Mengatur dokumen pengganti SKTM (seperti Kartu Jamkesmas/KIS/PKH), jangka waktu tanggapan permohonan bantuan hukum maksimal 3 hari kerja, dan ruang lingkup konsultasi hukum.',
                'body' => <<<'TEXT'
Pasal 6 Ayat (2)
Dalam hal Pemohon Bantuan Hukum tidak memiliki Surat Keterangan Tidak Mampu (SKTM), Pemohon dapat melampirkan Kartu Jaminan Kesehatan Masyarakat, Kartu Program Keluarga Harapan (PKH), Kartu Bantuan Langsung Tunai, atau dokumen resmi lain sebagai bukti ketidakmampuan ekonomi.
Pasal 9 Ayat (1)
Pemberi Bantuan Hukum wajib memeriksa kelengkapan persyaratan dalam waktu paling lama 1 (satu) hari kerja dan memberikan jawaban menerima atau menolak permohonan Bantuan Hukum paling lama 3 (tiga) hari kerja sejak persyaratan dinyatakan lengkap.
Pasal 16 Ayat (1)
Pemberian Bantuan Hukum secara nonlitigasi meliputi kegiatan penyuluhan hukum, konsultasi hukum, investigasi perkara, penelitian hukum, mediasi, negosiasi, pemberdayaan masyarakat, pendampingan di luar pengadilan, dan pembuatan dokumen hukum.
TEXT,
            ],
            [
                'id' => 'JDIH-012',
                'title' => 'Peraturan Pemerintah tentang Perjanjian Kerja Waktu Tertentu, Alih Daya, Waktu Kerja, dan Pemutusan Hubungan Kerja',
                'type' => 'Peraturan Pemerintah',
                'number' => 'PP No. 35 Tahun 2021',
                'year' => 2021,
                'category' => 'Ketenagakerjaan',
                'summary' => 'Mengatur prosedur Pemutusan Hubungan Kerja (PHK), kewajiban perundingan bipartit, uang kompensasi PKWT, serta hak uang pesangon, uang penghargaan masa kerja, dan uang penggantian hak.',
                'body' => <<<'TEXT'
Pasal 15 Ayat (1)
Pengusaha wajib memberikan uang kompensasi kepada Pekerja/Buruh yang hubungan kerjanya berdasarkan Perjanjian Kerja Waktu Tertentu (PKWT) pada saat berakhirnya PKWT apabila Pekerja telah mempunyai masa kerja paling sedikit 1 (satu) bulan secara terus menerus.
Pasal 37 Ayat (1)
Pengusaha, Pekerja/Buruh, Serikat Pekerja, dan Pemerintah harus mengupayakan agar tidak terjadi Pemutusan Hubungan Kerja (PHK). Apabila PHK tidak dapat dihindari, maksud dan alasan PHK diberitahukan secara tertulis oleh Pengusaha paling lambat 14 (empat belas) hari kerja sebelum PHK.
Pasal 39 Ayat (1)
Dalam hal Pekerja/Buruh menolak PHK, penyelesaian PHK wajib dilakukan melalui perundingan bipartit antara Pengusaha dengan Pekerja/Buruh dan/atau Serikat Pekerja, dan apabila tidak mencapai kesepakatan dilanjutkan ke tahap mediasi pada Dinas Ketenagakerjaan.
Pasal 40 Ayat (1)
Dalam hal terjadi Pemutusan Hubungan Kerja, Pengusaha wajib membayar uang pesangon dan/atau uang penghargaan masa kerja, dan uang penggantian hak yang seharusnya diterima sesuai dengan masa kerja Pekerja/Buruh.
TEXT,
            ],
            [
                'id' => 'JDIH-013',
                'title' => 'Peraturan Pemerintah tentang Pengupahan',
                'type' => 'Peraturan Pemerintah',
                'number' => 'PP No. 36 Tahun 2021',
                'year' => 2021,
                'category' => 'Ketenagakerjaan',
                'summary' => 'Mengatur perlindungan upah pekerja, larangan membayar upah di bawah Upah Minimum (UMP/UMK), batas waktu pembayaran upah, denda keterlambatan pembayaran gaji, dan batas pemotongan upah.',
                'body' => <<<'TEXT'
Pasal 23 Ayat (3)
Pengusaha dilarang membayar Upah lebih rendah dari Upah Minimum yang telah ditetapkan oleh Pemerintah Daerah (UMP atau UMK).
Pasal 55 Ayat (1)
Pengusaha wajib membayar Upah pada waktu yang telah diperjanjikan antara Pengusaha dengan Pekerja/Buruh secara tepat waktu dan penuh.
Pasal 61 Ayat (1)
Pengusaha yang karena kesengajaan atau kelalaiannya mengakibatkan keterlambatan pembayaran Upah, dikenakan denda sesuai dengan persentase tertentu dari Upah Pekerja/Buruh yang wajib dibayarkan kepada Pekerja/Buruh.
Pasal 65 Ayat (1)
Jumlah keseluruhan pemotongan Upah oleh Pengusaha paling banyak 50% (lima puluh persen) dari setiap pembayaran Upah yang seharusnya diterima Pekerja/Buruh.
TEXT,
            ],
            [
                'id' => 'JDIH-014',
                'title' => 'Peraturan Daerah tentang Penyelenggaraan Ketenagakerjaan dan Perlindungan Pekerja',
                'type' => 'Peraturan Daerah',
                'number' => 'Perda No. 4 Tahun 2022',
                'year' => 2022,
                'category' => 'Ketenagakerjaan',
                'summary' => 'Mengatur larangan perusahaan menahan dokumen asli pekerja seperti ijazah, KTP, atau akta kelahiran sebagai jaminan kerja serta mekanisme pengawasan ketenagakerjaan.',
                'body' => <<<'TEXT'
Pasal 19 Ayat (1)
Setiap Pengusaha atau pemberi kerja dilarang menahan atau menyimpan dokumen asli milik Pekerja/Buruh berupa ijazah pendidikan, Kartu Tanda Penduduk, Kartu Keluarga, paspor, atau sertifikat kompetensi sebagai syarat kerja maupun jaminan ikatan dinas.
Pasal 19 Ayat (2)
Dalam hal hubungan kerja berakhir karena alasan apa pun, Pengusaha wajib segera mengembalikan seluruh dokumen milik Pekerja/Buruh tanpa memungut biaya tebusan atau penalti yang bertentangan dengan hukum.
Pasal 42 Ayat (1)
Pekerja/Buruh yang mengalami penahanan dokumen asli atau pelanggaran hak normatif ketenagakerjaan dapat mengajukan pengaduan kepada Pengawas Ketenagakerjaan dan mediator Dinas Tenaga Kerja untuk dilakukan pemeriksaan dan pemanggilan pemberi kerja.
TEXT,
            ],
            [
                'id' => 'JDIH-015',
                'title' => 'Peraturan Pemerintah tentang Pendaftaran Tanah',
                'type' => 'Peraturan Pemerintah',
                'number' => 'PP No. 24 Tahun 1997',
                'year' => 1997,
                'category' => 'Pertanahan',
                'summary' => 'Mengatur penetapan batas bidang tanah secara kontradiktur delimitasi (persetujuan tetangga berbatasan), kekuatan pembuktian sertifikat hak atas tanah, dan penerbitan sertifikat pengganti karena hilang atau rusak.',
                'body' => <<<'TEXT'
Pasal 18 Ayat (1)
Penetapan batas bidang tanah yang sudah dipunyai dengan suatu hak dilakukan oleh Panitia Ajudikasi atau Kepala Kantor Pertanahan berdasarkan penunjukan batas oleh pemegang hak atas tanah yang bersangkutan dan sedapat mungkin disetujui oleh para pemilik tanah yang berbatasan.
Pasal 32 Ayat (1)
Sertipikat merupakan surat tanda bukti hak yang berlaku sebagai alat pembuktian yang kuat mengenai data fisik dan data yuridis yang termuat di dalamnya, sepanjang data fisik dan data yuridis tersebut sesuai dengan data yang ada dalam surat ukur dan buku tanah hak yang bersangkutan.
Pasal 57 Ayat (1)
Atas permohonan pemegang hak diterbitkan sertipikat baru sebagai pengganti sertipikat yang rusak atau hilang setelah pemohon menyerahkan surat keterangan kehilangan dari kepolisian dan mengucapkan sumpah di hadapan Kepala Kantor Pertanahan.
TEXT,
            ],
            [
                'id' => 'JDIH-016',
                'title' => 'Peraturan Menteri ATR/Kepala BPN tentang Penanganan dan Penyelesaian Kasus Pertanahan',
                'type' => 'Peraturan Menteri',
                'number' => 'Permen ATR/BPN No. 21 Tahun 2020',
                'year' => 2020,
                'category' => 'Pertanahan',
                'summary' => 'Mengatur tata cara pengaduan sengketa batas tanah, tumpang tindih sertifikat, penyerobotan lahan, penelitian lapangan, dan penyelesaian sengketa melalui mediasi.',
                'body' => <<<'TEXT'
Pasal 6 Ayat (1)
Pengaduan kasus pertanahan seperti sengketa batas bidang tanah, penguasaan tanah tanpa hak, atau tumpang tindih kepemilikan disampaikan secara tertulis kepada Kementerian, Kantor Wilayah, atau Kantor Pertanahan dengan melampirkan fotokopi identitas, bukti penguasaan/kepemilikan tanah, dan uraian kronologi sengketa.
Pasal 11 Ayat (1)
Terhadap pengaduan sengketa pertanahan yang memenuhi syarat, Kantor Pertanahan melakukan pengumpulan data fisik dan data yuridis serta pemeriksaan lapangan dan pengukuran ulang batas tanah apabila diperlukan.
Pasal 34 Ayat (1)
Penyelesaian Sengketa Pertanahan dapat dilakukan melalui Mediasi yang difasilitasi oleh Kantor Pertanahan atau pemerintah desa/kelurahan setempat untuk mencapai kesepakatan perdamaian yang dituangkan dalam Akta Perdamaian.
TEXT,
            ],
            [
                'id' => 'JDIH-017',
                'title' => 'Undang-Undang tentang Pelindungan Data Pribadi',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 27 Tahun 2022',
                'year' => 2022,
                'category' => 'Perlindungan Konsumen & Digital',
                'summary' => 'Mengatur hak subjek data pribadi, larangan memperoleh dan menyebarkan data pribadi secara melawan hukum (termasuk penyebaran kontak oleh penagih pinjaman online), serta ancaman pidana.',
                'body' => <<<'TEXT'
Pasal 12 Ayat (1)
Subjek Data Pribadi berhak menggugat dan menerima ganti rugi atas pelanggaran pemrosesan Data Pribadi tentang dirinya sesuai dengan ketentuan peraturan perundang-undangan.
Pasal 65 Ayat (1)
Setiap Orang dilarang secara melawan hukum memperoleh atau mengumpulkan Data Pribadi yang bukan miliknya dengan maksud untuk menguntungkan diri sendiri atau orang lain yang dapat mengakibatkan kerugian Subjek Data Pribadi.
Pasal 65 Ayat (2)
Setiap Orang dilarang secara melawan hukum mengungkapkan atau menyebarkan Data Pribadi yang bukan miliknya tanpa persetujuan sah dari Subjek Data Pribadi.
Pasal 67 Ayat (2)
Setiap Orang yang dengan sengaja dan melawan hukum mengungkapkan Data Pribadi yang bukan miliknya sebagaimana dimaksud dalam Pasal 65 ayat (2) dipidana dengan pidana penjara paling lama 4 (empat) tahun dan/atau pidana denda paling banyak Rp4.000.000.000,00.
TEXT,
            ],
            [
                'id' => 'JDIH-018',
                'title' => 'Peraturan Otoritas Jasa Keuangan tentang Pelindungan Konsumen dan Masyarakat di Sektor Jasa Keuangan',
                'type' => 'Peraturan OJK',
                'number' => 'POJK No. 22 Tahun 2023',
                'year' => 2023,
                'category' => 'Perlindungan Konsumen & Digital',
                'summary' => 'Mengatur etika penagihan utang/pinjaman, larangan ancaman, intimidasi, mempermalukan konsumen, atau menagih kepada pihak selain peminjam (kontak darurat).',
                'body' => <<<'TEXT'
Pasal 62 Ayat (1)
Pelaku Usaha Jasa Keuangan wajib memastikan bahwa penagihan kredit atau pembiayaan kepada Konsumen dilaksanakan sesuai dengan norma yang berlaku di masyarakat dan ketentuan peraturan perundang-undangan.
Pasal 62 Ayat (2)
Dalam melakukan penagihan, Pelaku Usaha Jasa Keuangan dan pihak ketiga penagih (debt collector) dilarang menggunakan cara ancaman, kekerasan, atau tindakan yang bersifat mempermalukan Konsumen, serta dilarang melakukan penagihan kepada pihak selain Konsumen yang berutang.
Pasal 62 Ayat (3)
Penagihan melalui sarana komunikasi hanya dapat dilakukan pada hari Senin sampai dengan Sabtu di luar hari libur nasional dari pukul 08.00 sampai dengan pukul 20.00 waktu setempat.
TEXT,
            ],
            [
                'id' => 'JDIH-019',
                'title' => 'Undang-Undang tentang Penghapusan Kekerasan Dalam Rumah Tangga',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 23 Tahun 2004',
                'year' => 2004,
                'category' => 'Perlindungan Perempuan & Anak',
                'summary' => 'Mengatur larangan kekerasan fisik, psikis, seksual, dan penelantaran rumah tangga, hak korban atas perlindungan sementara kepolisian dalam 1x24 jam, serta layanan pendampingan.',
                'body' => <<<'TEXT'
Pasal 5
Setiap orang dilarang melakukan kekerasan dalam rumah tangga terhadap orang dalam lingkup rumah tangganya, dengan cara kekerasan fisik, kekerasan psikis, kekerasan seksual, atau penelantaran rumah tangga.
Pasal 10
Korban berhak mendapatkan perlindungan dari pihak keluarga, kepolisian, kejaksaan, pengadilan, advokat, lembaga sosial, atau pihak lainnya baik sementara maupun berdasarkan penetapan perintah perlindungan dari pengadilan, serta pelayanan kesehatan sesuai dengan kebutuhan medis.
Pasal 16 Ayat (1)
Dalam waktu 1 x 24 (satu kali dua puluh empat) jam terhitung sejak mengetahui atau menerima laporan kekerasan dalam rumah tangga, kepolisian wajib segera memberikan perlindungan sementara pada korban.
Pasal 26 Ayat (1)
Korban berhak melaporkan secara langsung kekerasan dalam rumah tangga kepada kepolisian baik di tempat korban berada maupun di tempat kejadian perkara, atau memberikan kuasa kepada keluarga atau pendamping untuk melaporkannya.
TEXT,
            ],
            [
                'id' => 'JDIH-020',
                'title' => 'Undang-Undang tentang Keterbukaan Informasi Publik',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 14 Tahun 2008',
                'year' => 2008,
                'category' => 'Keterbukaan Informasi & Anggaran',
                'summary' => 'Mengatur hak setiap warga negara memperoleh informasi publik termasuk laporan anggaran dan kegiatan badan publik, batas waktu tanggapan PPID 10 hari kerja, dan pengajuan keberatan.',
                'body' => <<<'TEXT'
Pasal 4 Ayat (1)
Setiap Orang berhak memperoleh Informasi Publik sesuai dengan ketentuan Undang-Undang ini, termasuk melihat dan mengetahui Informasi Publik serta mendapatkan salinan Informasi Publik melalui permohonan.
Pasal 9 Ayat (2)
Badan Publik wajib mengumumkan secara berkala Informasi Publik yang meliputi informasi tentang profil Badan Publik, ringkasan informasi tentang program dan/atau kegiatan yang sedang dijalankan, serta ringkasan laporan keuangan dan realisasi anggaran.
Pasal 22 Ayat (7)
Paling lambat 10 (sepuluh) hari kerja sejak diterimanya permintaan, Badan Publik yang bersangkutan wajib menyampaikan pemberitahuan tertulis dan dapat memperpanjang waktu paling lambat 7 (tujuh) hari kerja.
Pasal 35 Ayat (1)
Setiap Pemohon Informasi Publik dapat mengajukan keberatan secara tertulis kepada atasan Pejabat Pengelola Informasi dan Dokumentasi (PPID) apabila permohonan informasinya ditolak atau tidak ditanggapi sebagaimana mestinya.
TEXT,
            ],
            [
                'id' => 'JDIH-021',
                'title' => 'Undang-Undang tentang Perlindungan Konsumen',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 8 Tahun 1999',
                'year' => 1999,
                'category' => 'Perlindungan Konsumen & Digital',
                'summary' => 'Mengatur hak konsumen atas barang/jasa yang aman dan sesuai deskripsi, kewajiban pelaku usaha memberikan ganti rugi atau penggantian barang cacat dalam 7 hari, dan penyelesaian sengketa di BPSK.',
                'body' => <<<'TEXT'
Pasal 4
Hak konsumen adalah hak atas kenyamanan, keamanan, dan keselamatan dalam mengkonsumsi barang dan/atau jasa; hak untuk memilih barang dan/atau jasa; hak atas informasi yang benar, jelas, dan jujur mengenai kondisi dan jaminan barang dan/atau jasa; serta hak untuk mendapatkan kompensasi, ganti rugi dan/atau penggantian, apabila barang dan/atau jasa yang diterima tidak sesuai dengan perjanjian.
Pasal 19 Ayat (1)
Pelaku usaha bertanggung jawab memberikan ganti rugi atas kerusakan, pencemaran, dan/atau kerugian konsumen akibat mengkonsumsi barang dan/atau jasa yang dihasilkan atau diperdagangkan.
Pasal 19 Ayat (3)
Pemberian ganti rugi berupa pengembalian uang atau penggantian barang dan/atau jasa yang sejenis atau setara nilainya dilaksanakan dalam tenggang waktu 7 (tujuh) hari setelah tanggal transaksi.
Pasal 45 Ayat (1)
Setiap konsumen yang dirugikan dapat menggugat pelaku usaha melalui lembaga yang bertugas menyelesaikan sengketa antara konsumen dan pelaku usaha (Badan Penyelesaian Sengketa Konsumen / BPSK) atau melalui peradilan umum.
TEXT,
            ],
            [
                'id' => 'JDIH-022',
                'title' => 'Undang-Undang tentang Desa dan Transparansi Pemerintahan Desa',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 6 Tahun 2014',
                'year' => 2014,
                'category' => 'Keterbukaan Informasi & Anggaran',
                'summary' => 'Mengatur hak masyarakat desa untuk meminta dan mendapatkan informasi penyelenggaraan Pemerintahan Desa, penggunaan Dana Desa/APBDes, dan pendataan bantuan sosial secara transparan.',
                'body' => <<<'TEXT'
Pasal 26 Ayat (4) huruf f
Dalam melaksanakan tugas, Kepala Desa berkewajiban melaksanakan prinsip tata Pemerintahan Desa yang akuntabel, transparan, profesional, efektif dan efisien, bersih, serta bebas dari kolusi, korupsi, dan nepotisme.
Pasal 68 Ayat (1)
Masyarakat Desa berhak meminta dan mendapatkan informasi dari Pemerintah Desa serta mengawasi kegiatan penyelenggaraan Pemerintahan Desa, pelaksanaan Pembangunan Desa, pembinaan kemasyarakatan Desa, dan pemberdayaan masyarakat Desa.
Pasal 82 Ayat (1)
Masyarakat Desa berhak mendapatkan informasi mengenai rencana dan pelaksanaan Pembangunan Desa serta laporan realisasi Anggaran Pendapatan dan Belanja Desa (APBDes) dan penetapan keluarga penerima manfaat bantuan sosial.
TEXT,
            ],
            [
                'id' => 'JDIH-023',
                'title' => 'Undang-Undang tentang Perlindungan dan Pengelolaan Lingkungan Hidup',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 32 Tahun 2009',
                'year' => 2009,
                'category' => 'Infrastruktur',
                'summary' => 'Mengatur hak warga atas lingkungan hidup yang baik dan sehat, larangan pembuangan limbah tanpa izin ke sungai/drainase, dan hak mengajukan pengaduan pencemaran lingkungan.',
                'body' => <<<'TEXT'
Pasal 65 Ayat (1)
Setiap orang berhak atas lingkungan hidup yang baik dan sehat sebagai bagian dari hak asasi manusia serta berhak mengajukan usul dan/atau keberatan terhadap rencana usaha dan/atau kegiatan yang diperkirakan dapat menimbulkan dampak terhadap lingkungan hidup.
Pasal 65 Ayat (5)
Setiap orang berhak melakukan pengaduan akibat dugaan pencemaran dan/atau perusakan lingkungan hidup kepada instansi lingkungan hidup daerah.
Pasal 66
Setiap orang yang memperjuangkan hak atas lingkungan hidup yang baik dan sehat tidak dapat dituntut secara pidana maupun digugat secara perdata.
Pasal 69 Ayat (1)
Setiap orang dilarang melakukan perbuatan yang mengakibatkan pencemaran dan/atau perusakan lingkungan hidup serta membuang limbah ke media lingkungan hidup tanpa izin.
TEXT,
            ],
            [
                'id' => 'JDIH-024',
                'title' => 'Peraturan Pemerintah tentang Disiplin Pegawai Negeri Sipil',
                'type' => 'Peraturan Pemerintah',
                'number' => 'PP No. 94 Tahun 2021',
                'year' => 2021,
                'category' => 'Dugaan Pungutan Liar',
                'summary' => 'Mengatur kewajiban PNS memberikan pelayanan sebaik-baiknya kepada masyarakat dan larangan menyalahgunakan wewenang atau memungut imbalan di luar ketentuan.',
                'body' => <<<'TEXT'
Pasal 3 huruf f
PNS wajib menunjukkan integritas dan keteladanan dalam sikap, perilaku, ucapan, dan tindakan kepada setiap orang, baik di dalam maupun di luar kedinasan.
Pasal 4 huruf f
PNS wajib memberikan pelayanan sebaik-baiknya kepada masyarakat sesuai standar prosedur operasional dan ketentuan perundang-undangan.
Pasal 5 huruf a
PNS dilarang menyalahgunakan wewenang, menjadi perantara untuk mendapatkan keuntungan pribadi dan/atau orang lain dengan menggunakan kewenangan orang lain, serta meminta atau menerima hadiah yang berhubungan dengan jabatannya.
Pasal 8 Ayat (1)
PNS yang tidak menaati ketentuan kewajiban dan larangan dijatuhi Hukuman Disiplin mulai dari hukuman disiplin ringan, sedang, hingga pemberhentian sebagai PNS.
TEXT,
            ],
            [
                'id' => 'JDIH-025',
                'title' => 'Undang-Undang tentang Perlindungan Saksi dan Korban',
                'type' => 'Undang-Undang',
                'number' => 'UU No. 31 Tahun 2014',
                'year' => 2014,
                'category' => 'Bantuan Hukum',
                'summary' => 'Mengatur perlindungan hukum bagi Saksi, Korban, dan Pelapor (Whistleblower) yang melaporkan tindak pidana dengan iktikad baik agar bebas dari ancaman fisik maupun tuntutan balik.',
                'body' => <<<'TEXT'
Pasal 5 Ayat (1)
Saksi dan Korban berhak memperoleh perlindungan atas keamanan pribadi, keluarga, dan harta bendanya, serta bebas dari ancaman yang berkenaan dengan kesaksian yang akan, sedang, atau telah diberikannya.
Pasal 10 Ayat (1)
Saksi, Korban, Saksi Pelaku, dan/atau Pelapor tidak dapat dituntut secara hukum, baik pidana maupun perdata atas kesaksian dan/atau laporan yang akan, sedang, atau telah diberikannya, kecuali laporan tersebut diberikan tidak dengan iktikad baik.
Pasal 10 Ayat (2)
Dalam hal terdapat tuntutan hukum terhadap Saksi, Korban, dan/atau Pelapor atas laporan yang diberikannya dengan iktikad baik, tuntutan hukum tersebut wajib ditunda hingga kasus yang ia laporkan telah diputus oleh pengadilan dan memperoleh kekuatan hukum tetap.
TEXT,
            ],
        ];

        // Tambahkan 55 dokumen JDIH tematik (JDIH-026 s.d. JDIH-080) sehingga tepat 80 dokumen JDIH
        $thematicTemplates = [
            [
                'category' => 'Administrasi Kependudukan',
                'type' => 'Peraturan Menteri Dalam Negeri',
                'prefix' => 'Permendagri No.',
                'title_base' => 'Petunjuk Teknis Pelayanan Pendaftaran Penduduk dan Pencatatan Sipil',
                'summary' => 'Mengatur tata cara teknis penerbitan KTP-el, Kartu Identitas Anak (KIA), Surat Keterangan Pindah, Akta Kelahiran, dan layanan jemput bola kependudukan.',
                'pasal_1' => 'Dinas Kependudukan dan Pencatatan Sipil wajib menyelenggarakan pelayanan terpadu dan daring tanpa memungut biaya administrasi maupun denda keterlambatan pelaporan.',
                'pasal_2' => 'Dalam hal blangko KTP-el mengalami keterbatasan ketersediaan, Disdukcapil wajib menerbitkan Identitas Kependudukan Digital (IKD) atau Biodata Penduduk yang memiliki kekuatan hukum setara.',
            ],
            [
                'category' => 'Pelayanan Kesehatan',
                'type' => 'Peraturan Menteri Kesehatan',
                'prefix' => 'Permenkes No.',
                'title_base' => 'Standar Pelayanan Kegawatdaruratan Medis dan Rujukan Pasien',
                'summary' => 'Mengatur triase pasien IGD, kewajiban stabilisasi pasien darurat sebelum rujukan, layanan ambulans, dan hak informasi medis pasien.',
                'pasal_1' => 'Setiap Instalasi Gawat Darurat (IGD) wajib melakukan pemeriksaan triase dalam waktu kurang dari 5 (lima) menit sejak pasien tiba dan segera memberikan tindakan penyelamatan nyawa.',
                'pasal_2' => 'Rumah Sakit dilarang merujuk pasien gawat darurat ke fasilitas kesehatan lain sebelum kondisi klinis pasien distabilkan dan dipastikan ketersediaan tempat tidur di rumah sakit tujuan.',
            ],
            [
                'category' => 'Infrastruktur',
                'type' => 'Peraturan Daerah',
                'prefix' => 'Perda No.',
                'title_base' => 'Penyelenggaraan Prasarana Jalan Lingkungan, Drainase, dan Penerangan Umum',
                'summary' => 'Mengatur tanggung jawab pemerintah daerah dalam pemeliharaan jalan lingkungan, saluran drainase pencegahan banjir, dan penanganan aduan kerusakan infrastruktur.',
                'pasal_1' => 'Pemerintah Daerah melalui Dinas Pekerjaan Umum wajib melakukan pemeriksaan lapangan paling lambat 3 (tiga) hari kerja setelah menerima laporan masyarakat mengenai jalan rusak berlubang atau drainase tersumbat.',
                'pasal_2' => 'Perbaikan darurat terhadap kerusakan jalan atau jembatan yang membahayakan keselamatan warga wajib segera dipasang rambu pengaman dan ditangani melalui anggaran pemeliharaan rutin.',
            ],
            [
                'category' => 'Dugaan Pungutan Liar',
                'type' => 'Peraturan Bupati',
                'prefix' => 'Perbup No.',
                'title_base' => 'Pedoman Pengawasan Internal, Pencegahan Pungutan Liar, dan Pengelolaan Pengaduan',
                'summary' => 'Mengatur mekanisme pelaporan pungutan liar di unit pelayanan daerah, perlindungan kerahasiaan pelapor, dan pemeriksaan oleh Inspektorat Daerah.',
                'pasal_1' => 'Setiap unit penyelenggara pelayanan publik wajib memasang maklumat pelayanan yang memuat rincian tarif resmi Rp0 (gratis) untuk layanan dasar serta kanal resmi pengaduan pungutan liar.',
                'pasal_2' => 'Laporan masyarakat mengenai permintaan uang pelicin, biaya tambahan tanpa kuitansi resmi, atau percaloan wajib ditindaklanjuti oleh Inspektorat Daerah dalam waktu paling lama 5 (lima) hari kerja.',
            ],
            [
                'category' => 'Bantuan Hukum',
                'type' => 'Peraturan Daerah',
                'prefix' => 'Perda No.',
                'title_base' => 'Penyelenggaraan Bantuan Hukum bagi Masyarakat Miskin dan Kelompok Rentan',
                'summary' => 'Mengatur fasilitasi anggaran daerah untuk pendampingan hukum gratis melalui Organisasi Bantuan Hukum (OBH) terakreditasi bagi warga tidak mampu.',
                'pasal_1' => 'Pemerintah Daerah menyediakan pembiayaan Bantuan Hukum bagi warga miskin yang berhadapan dengan hukum baik sebagai korban, saksi, penggugat, tergugat, maupun tersangka.',
                'pasal_2' => 'Warga cukup melampirkan Kartu Tanda Penduduk, Surat Keterangan Tidak Mampu (SKTM) atau kartu bantuan sosial, serta kronologi permasalahan untuk memperoleh pendampingan advokat OBH secara gratis.',
            ],
            [
                'category' => 'Ketenagakerjaan',
                'type' => 'Peraturan Menteri Ketenagakerjaan',
                'prefix' => 'Permenaker No.',
                'title_base' => 'Tata Cara Mediasi Perselisihan Hubungan Industrial dan Perlindungan Hak Pekerja',
                'summary' => 'Mengatur prosedur pencatatan perselisihan hak, perselisihan PHK, pembayaran Tunjangan Hari Raya (THR), dan pemeriksaan oleh mediator hubungan industrial.',
                'pasal_1' => 'Apabila perundingan bipartit tidak mencapai kesepakatan dalam waktu 30 (tiga puluh) hari kerja, salah satu atau kedua belah pihak mencatatkan perselisihan kepada instansi ketenagakerjaan setempat dengan melampirkan bukti perundingan.',
                'pasal_2' => 'Mediator Hubungan Industrial wajib mengeluarkan anjuran tertulis paling lambat 10 (sepuluh) hari kerja sejak sidang mediasi pertama untuk menyelesaikan sengketa upah, pesangon, atau PHK.',
            ],
            [
                'category' => 'Pertanahan',
                'type' => 'Peraturan Pemerintah',
                'prefix' => 'PP No.',
                'title_base' => 'Hak Pengelolaan, Hak Atas Tanah, dan Mediasi Sengketa Batas Bidang Tanah',
                'summary' => 'Mengatur pendaftaran tanah sistematis lengkap (PTSL), penyelesaian keberatan batas tanah secara musyawarah di tingkat desa/kelurahan, dan perlindungan pemegang hak.',
                'pasal_1' => 'Dalam hal terdapat keberatan atau perselisihan mengenai tanda batas bidang tanah antara tetangga yang berbatasan, Kepala Desa/Lurah memfasilitasi musyawarah penetapan batas bersama para pihak dan saksi batas.',
                'pasal_2' => 'Setiap perubahan atau pemindahan tanda batas tanah (patok) secara sepihak tanpa persetujuan pemilik berbatasan dan petugas ukur berwenang tidak sah secara hukum dan dapat dilaporkan untuk pemulihan batas.',
            ],
            [
                'category' => 'Perlindungan Konsumen & Digital',
                'type' => 'Undang-Undang',
                'prefix' => 'UU No.',
                'title_base' => 'Informasi dan Transaksi Elektronik serta Perlindungan dari Penipuan Daring',
                'summary' => 'Mengatur keabsahan bukti elektronik, larangan penyebaran berita bohong yang merugikan konsumen dalam transaksi elektronik, serta pelaporan ancaman dan pemerasan digital.',
                'pasal_1' => 'Informasi Elektronik dan/atau Dokumen Elektronik seperti tangkapan layar percakapan, bukti transfer, dan rekaman elektronik merupakan alat bukti hukum yang sah dalam laporan pengaduan maupun peradilan.',
                'pasal_2' => 'Setiap orang dilarang dengan sengaja dan tanpa hak menyebarkan berita bohong yang mengakibatkan kerugian konsumen dalam Transaksi Elektronik atau mengirimkan informasi yang berisi ancaman kekerasan dan pemerasan.',
            ],
            [
                'category' => 'Perlindungan Perempuan & Anak',
                'type' => 'Undang-Undang',
                'prefix' => 'UU No.',
                'title_base' => 'Perlindungan Anak dan Layanan Terpadu Korban Kekerasan',
                'summary' => 'Mengatur perlindungan khusus bagi anak dan perempuan korban kekerasan, hak visum dan pemulihan psikologis gratis, serta pendampingan oleh UPTD PPA.',
                'pasal_1' => 'Pemerintah Daerah wajib menyelenggarakan layanan terpadu bagi perempuan dan anak korban kekerasan yang meliputi pengaduan, penjangkauan, pengelolaan kasus, penampungan sementara (rumah aman), mediasi, dan pendampingan hukum.',
                'pasal_2' => 'Biaya pemeriksaan medis, visum et repertum, dan pemulihan psikologis bagi korban kekerasan dalam rumah tangga serta kekerasan terhadap anak yang melapor melalui UPTD PPA ditanggung oleh pemerintah.',
            ],
            [
                'category' => 'Keterbukaan Informasi & Anggaran',
                'type' => 'Peraturan Menteri Dalam Negeri',
                'prefix' => 'Permendagri No.',
                'title_base' => 'Pengelolaan Keuangan Desa dan Transparansi Penyaluran Bantuan Sosial',
                'summary' => 'Mengatur kewajiban publikasi baliho/informasi APBDes, musyawarah desa penetapan penerima bantuan sosial, dan saluran pengaduan penyimpangan anggaran.',
                'pasal_1' => 'Pemerintah Desa wajib mempublikasikan ringkasan Anggaran Pendapatan dan Belanja Desa (APBDes) serta laporan realisasi pelaksanaan kegiatan melalui media informasi yang mudah diakses oleh seluruh warga desa.',
                'pasal_2' => 'Warga yang menemukan ketidaksesuaian data penerima bantuan sosial atau indikasi pemotongan dana bantuan berhak menyampaikan sanggahan dan pengaduan melalui musyawarah desa maupun portal pengaduan daerah.',
            ],
        ];

        $documents = [];
        foreach ($coreDocuments as $doc) {
            $documents[] = [
                'id' => $doc['id'],
                'title' => $doc['title'],
                'type' => $doc['type'],
                'number' => $doc['number'],
                'year' => $doc['year'],
                'category' => $doc['category'],
                'summary' => $doc['summary'],
                'body' => $doc['body'],
                'source_url' => '/rujukan/'.$doc['id'],
                'filename' => strtolower($doc['id']).'.pdf',
                'extraction' => 'Berhasil',
                'status' => 'Terindeks',
                'updated' => $today,
            ];
        }

        for ($i = 26; $i <= 80; $i++) {
            $tpl = $thematicTemplates[($i - 26) % count($thematicTemplates)];
            $id = sprintf('JDIH-%03d', $i);
            $year = 2015 + ($i % 12);
            $num = ($i * 2) - 35;
            $articleBase = 10 + ($i % 25);

            $body = sprintf(
                "Pasal %d Ayat (1)\n%s\nPasal %d Ayat (2)\n%s\nPasal %d Ayat (1)\nSetiap warga masyarakat yang mengalami kendala pada layanan %s berhak menyampaikan laporan disertai kronologi kejadian melalui kanal layanan BHIMA untuk memperoleh verifikasi dan tindak lanjut resmi.",
                $articleBase,
                $tpl['pasal_1'],
                $articleBase,
                $tpl['pasal_2'],
                $articleBase + 1,
                strtolower($tpl['category'])
            );

            $documents[] = [
                'id' => $id,
                'title' => $tpl['title_base'].' Seri '.$num,
                'type' => $tpl['type'],
                'number' => sprintf('%s %d Tahun %d', $tpl['prefix'], $num, $year),
                'year' => $year,
                'category' => $tpl['category'],
                'summary' => $tpl['summary'],
                'body' => $body,
                'source_url' => '/rujukan/'.$id,
                'filename' => strtolower($id).'.pdf',
                'extraction' => 'Berhasil',
                'status' => 'Terindeks',
                'updated' => $today,
            ];
        }

        return $documents;
    }

    /**
     * Bangun 20 dokumen SOP Layanan (SOP-001 s.d. SOP-020).
     *
     * @return list<array<string, mixed>>
     */
    public function buildSopContents(string $today): array
    {
        $sops = [
            [
                'id' => 'SOP-001',
                'title' => 'SOP Pelayanan Penerbitan KTP Elektronik (KTP-el) Baru, Rusak, atau Hilang',
                'number' => 'SOP/DUKCAPIL/001/2025',
                'year' => 2025,
                'topic' => 'Administrasi Kependudukan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Persyaratan Layanan KTP-el
Warga pemohon KTP-el baru cukup membawa fotokopi Kartu Keluarga (KK) tanpa surat pengantar RT/RW. Untuk KTP-el rusak wajib membawa fisik KTP-el yang rusak, sedangkan untuk KTP-el hilang wajib melampirkan Surat Keterangan Kehilangan dari Kepolisian.
Pasal 2 Ayat (1) Prosedur dan Standar Waktu Penyelesaian
Petugas loket Disdukcapil atau Kecamatan memverifikasi berkas dan melakukan perekaman/pencetakan. Penerbitan KTP-el selesai dalam waktu 1 (satu) sampai paling lambat 3 (tiga) hari kerja sejak berkas dinyatakan lengkap.
Pasal 3 Ayat (1) Biaya Pelayanan dan Pengaduan Keterlambatan
Biaya pelayanan penerbitan KTP-el adalah Rp0 (Gratis). Apabila permohonan melebihi batas waktu atau terdapat petugas yang meminta biaya tambahan, warga dapat langsung melaporkan nomor permohonan melalui Portal Pengaduan BHIMA atau loket pengaduan Disdukcapil.
TEXT,
            ],
            [
                'id' => 'SOP-002',
                'title' => 'SOP Penerbitan Kartu Keluarga (KK), Akta Kelahiran, dan Akta Kematian',
                'number' => 'SOP/DUKCAPIL/002/2025',
                'year' => 2025,
                'topic' => 'Administrasi Kependudukan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Persyaratan Pencatatan Sipil
Permohonan Akta Kelahiran melampirkan Surat Keterangan Lahir dari fasilitas kesehatan/bidan, buku nikah/kutipan akta perkawinan orang tua, dan Kartu Keluarga. Saksi pencatatan sipil disediakan oleh petugas dinas tanpa membebani pemohon.
Pasal 2 Ayat (1) Waktu Penyelesaian dan Pengiriman Dokumen
Dokumen Kartu Keluarga dan Akta Pencatatan Sipil diterbitkan dalam format digital bertanda tangan elektronik (TTE) paling lambat 3 (tiga) hari kerja dan dapat dicetak mandiri oleh warga pada kertas HVS A4 80 gram tanpa dipungut biaya.
TEXT,
            ],
            [
                'id' => 'SOP-003',
                'title' => 'SOP Penanganan Pengaduan Dugaan Pungutan Liar (Pungli) Layanan Publik',
                'number' => 'SOP/BHIMA-INSPEKTORAT/003/2025',
                'year' => 2025,
                'topic' => 'Dugaan Pungutan Liar',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Cara Pelaporan Dugaan Pungutan Liar
Warga yang dimintai uang rokok, uang pelicin, atau biaya di luar tarif resmi oleh oknum petugas dapat mengirimkan pengaduan melalui Portal BHIMA (bisa memilih mode Pelapor Anonim) dengan mencantumkan lokasi layanan, waktu kejadian, nama/ciri oknum bila diketahui, dan kronologi kejadian.
Pasal 2 Ayat (1) Bukti Pendukung dan Verifikasi Cepat
Warga dianjurkan melampirkan bukti pendukung bila ada (seperti foto tanda terima tidak resmi, tangkapan layar pesan, atau keterangan saksi). Petugas Verifikator BHIMA memverifikasi laporan paling lambat 2 x 24 jam untuk diteruskan kepada Inspektorat Daerah dan Unit Saber Pungli.
Pasal 3 Ayat (1) Jaminan Kerahasiaan dan Kelanjutan Layanan
Identitas pelapor dijamin kerahasiaannya. Layanan administrasi yang sedang diurus warga tetap diproses hingga selesai tanpa diskriminasi maupun hambatan dari unit terlapor.
TEXT,
            ],
            [
                'id' => 'SOP-004',
                'title' => 'SOP Penanganan Keluhan Pelayanan Gawat Darurat dan Kepesertaan BPJS Kesehatan',
                'number' => 'SOP/DINKES/004/2025',
                'year' => 2025,
                'topic' => 'Pelayanan Kesehatan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Penanganan Pasien Gawat Darurat di IGD
Setiap pasien dalam kondisi gawat darurat wajib langsung dilayani di IGD Puskesmas atau Rumah Sakit tanpa surat rujukan dan tanpa permintaan uang muka (deposit). Apabila keluarga pasien mengalami penolakan atau permintaan deposit, segera hubungi Petugas Pengaduan Rumah Sakit (PIPP) atau laporkan melalui BHIMA.
Pasal 2 Ayat (1) Reaktivasi Cepat BPJS PBI bagi Warga Tidak Mampu
Bagi warga tidak mampu yang kartu BPJS PBI-nya berstatus nonaktif saat membutuhkan rawat inap darurat, keluarga dapat mengurus Surat Keterangan Tidak Mampu (SKTM) dari Desa/Kelurahan dan mengajukan reaktivasi darurat ke Dinas Sosial dan Dinas Kesehatan dalam waktu maksimal 3 x 24 jam hari kerja.
TEXT,
            ],
            [
                'id' => 'SOP-005',
                'title' => 'SOP Pelaporan dan Tindak Lanjut Kerusakan Jalan, Jembatan, dan Drainase Lingkungan',
                'number' => 'SOP/PUPR/005/2025',
                'year' => 2025,
                'topic' => 'Infrastruktur',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Penyampaian Laporan Kerusakan Infrastruktur
Warga dapat melaporkan jalan rusak berlubang, jembatan membahayakan, lampu penerangan jalan mati, atau saluran drainase tersumbat melalui Portal BHIMA dengan mencantumkan titik lokasi (nama jalan, RT/RW, desa/kelurahan), foto kondisi kerusakan, dan dampak yang ditimbulkan.
Pasal 2 Ayat (1) Tahapan Verifikasi dan Disposisi Teknis
Verifikator BHIMA memeriksa kelengkapan laporan dalam 1 (satu) hari kerja dan mendisposisikan laporan ke Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) atau Pemerintah Desa sesuai kewenangan ruas jalan.
Pasal 3 Ayat (1) Pemasangan Rambu Darurat dan Perbaikan
Tim reaksi cepat Dinas PUPR melakukan survei lokasi dan pemasangan tanda peringatan darurat maksimal 2 (dua) hari kerja sejak disposisi diterima, dilanjutkan dengan penjadwalan perbaikan fisik.
TEXT,
            ],
            [
                'id' => 'SOP-006',
                'title' => 'SOP Permohonan Layanan Bantuan Hukum Gratis (Posbakum dan OBH Terakreditasi)',
                'number' => 'SOP/HUKUM/006/2025',
                'year' => 2025,
                'topic' => 'Bantuan Hukum',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Syarat Pengajuan Bantuan Hukum Gratis
Warga kurang mampu yang menghadapi permasalahan hukum (pidana, perdata, atau tata usaha negara) menyiapkan fotokopi KTP, Kartu Keluarga, Surat Keterangan Tidak Mampu (SKTM) dari Kepala Desa/Lurah atau Kartu KIS/PKH, serta ringkasan kronologi kasus.
Pasal 2 Ayat (1) Alur Pendaftaran dan Penunjukan Pendamping Hukum
Permohonan diajukan melalui Bagian Hukum Pemerintah Daerah, Pos Bantuan Hukum (Posbakum) Pengadilan, atau dirujuk melalui Portal BHIMA. Verifikasi permohonan dilakukan paling lama 3 (tiga) hari kerja sebelum ditunjuk Advokat/Paralegal dari Organisasi Bantuan Hukum (OBH) terakreditasi.
Pasal 3 Ayat (1) Ruang Lingkup Pendampingan Tanpa Biaya
Pendampingan meliputi konsultasi hukum, penyusunan dokumen hukum (gugatan/pledoi/permohonan), mediasi di luar pengadilan, hingga pendampingan persidangan tanpa dipungut biaya apa pun dari warga penerima bantuan.
TEXT,
            ],
            [
                'id' => 'SOP-007',
                'title' => 'SOP Pengaduan Perselisihan Hubungan Industrial, Upah, dan Penahanan Ijazah Pekerja',
                'number' => 'SOP/DISNAKER/007/2025',
                'year' => 2025,
                'topic' => 'Ketenagakerjaan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Langkah Awal Perundingan Bipartit
Pekerja yang mengalami PHK sepihak, gaji/upah belum dibayar, atau pembayaran di bawah UMK terlebih dahulu mengajukan permohonan perundingan bipartit secara tertulis kepada manajemen perusahaan dan menyimpan tanda terima surat.
Pasal 2 Ayat (1) Pencatatan Pengaduan ke Dinas Ketenagakerjaan
Apabila perusahaan menolak berunding atau tidak tercapai kesepakatan, serta dalam kasus penahanan ijazah asli pekerja, warga melapor ke Dinas Tenaga Kerja atau melalui Portal BHIMA dengan melampirkan KTP, perjanjian kerja/slip gaji, dan bukti perundingan bipartit.
Pasal 3 Ayat (1) Pemanggilan dan Mediasi Ketenagakerjaan
Pengawas Ketenagakerjaan dan Mediator memanggil pihak perusahaan paling lambat 7 (tujuh) hari kerja setelah berkas pengaduan lengkap untuk pemeriksaan normatif dan sidang mediasi.
TEXT,
            ],
            [
                'id' => 'SOP-008',
                'title' => 'SOP Fasilitasi Mediasi Sengketa Batas Tanah dan Pengurusan Surat Pertanahan Desa/Kelurahan',
                'number' => 'SOP/PERTANAHAN/008/2025',
                'year' => 2025,
                'topic' => 'Pertanahan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pengajuan Permohonan Mediasi Batas Tanah
Warga yang mengalami sengketa batas bidang tanah atau penyerobotan patok batas mengajukan permohonan mediasi tertulis kepada Kepala Desa/Lurah dengan melampirkan fotokopi KTP, bukti kepemilikan/penguasaan tanah (Sertifikat/Girik/SPPT PBB), dan sketsa lokasi.
Pasal 2 Ayat (1) Peninjauan Lapangan dan Musyawarah Desa
Pemerintah Desa/Kelurahan mengundang kedua belah pihak yang berbatasan beserta saksi tetangga batas untuk melakukan peninjauan lokasi bersama dalam waktu 7 (tujuh) hari kerja.
Pasal 3 Ayat (1) Berita Acara Kesepakatan atau Rujukan ke Kantor Pertanahan (BPN)
Apabila tercapai kesepakatan, dibuat Berita Acara Kesepakatan Batas. Apabila belum sepakat, Pemerintah Desa menerbitkan surat pengantar agar warga dapat mengajukan permohonan pengukuran pengembalian batas resmi dan mediasi ke Kantor Pertanahan (BPN).
TEXT,
            ],
            [
                'id' => 'SOP-009',
                'title' => 'SOP Pelaporan Penagihan Pinjaman Online Ilegal, Teror Debt Collector, dan Pelanggaran Data Pribadi',
                'number' => 'SOP/KOMINFO/009/2025',
                'year' => 2025,
                'topic' => 'Perlindungan Konsumen & Digital',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pengumpulan Bukti Elektronik Teror dan Penyebaran Data
Warga yang mengalami ancaman, intimidasi penagihan, atau penyebaran data pribadi oleh penyelenggara pinjaman online (pinjol) wajib menyimpan bukti berupa tangkapan layar (screenshot) pesan ancaman, nomor telepon penagih, nama aplikasi, dan bukti transaksi.
Pasal 2 Ayat (1) Alur Pengaduan Terpadu
Warga menyampaikan pengaduan melalui Portal BHIMA untuk diteruskan ke Satgas PASTI / OJK (Kontak 157) guna pemblokiran aplikasi, serta ke Sentra Pelayanan Kepolisian Terpadu (SPKT) apabila terdapat unsur tindak pidana pemerasan dan pelanggaran UU Pelindungan Data Pribadi.
TEXT,
            ],
            [
                'id' => 'SOP-010',
                'title' => 'SOP Layanan Cepat Pengaduan Kekerasan Dalam Rumah Tangga (KDRT) dan Perlindungan Anak (UPTD PPA)',
                'number' => 'SOP/UPTD-PPA/010/2025',
                'year' => 2025,
                'topic' => 'Perlindungan Perempuan & Anak',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Penerimaan Laporan Darurat Korban Kekerasan
Korban, keluarga, atau tetangga yang mengetahui terjadinya KDRT atau kekerasan terhadap perempuan dan anak dapat segera melapor melalui UPTD Perlindungan Perempuan dan Anak (PPA), Kepolisian (Unit PPA), atau Portal Pengaduan BHIMA dengan prioritas penanganan Tinggi.
Pasal 2 Ayat (1) Penjangkauan, Visum Gratis, dan Rumah Aman
Petugas UPTD PPA dalam waktu 1 x 24 jam memberikan pendampingan darurat meliputi rujukan pemeriksaan medis/visum gratis di RSUD, penempatan di Rumah Aman (Shelter) bila keselamatan korban terancam, konseling psikologis, dan pendampingan pelaporan kepolisian.
TEXT,
            ],
            [
                'id' => 'SOP-011',
                'title' => 'SOP Permohonan Informasi Publik (PPID) dan Pengajuan Keberatan Informasi Anggaran',
                'number' => 'SOP/PPID/011/2025',
                'year' => 2025,
                'topic' => 'Keterbukaan Informasi & Anggaran',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Tata Cara Permohonan Informasi Publik dan Anggaran
Warga dapat mengajukan permohonan informasi dokumen anggaran (APBD/APBDes), rincian realisasi kegiatan, atau kriteria penerima bantuan sosial melalui meja layanan PPID atau menu Transparansi dan Pengaduan pada Portal BHIMA dengan mencantumkan rincian informasi yang diminta dan tujuan penggunaan.
Pasal 2 Ayat (1) Batas Waktu Tanggapan dan Pengajuan Keberatan
PPID wajib memberikan jawaban tertulis paling lambat 10 (sepuluh) hari kerja. Apabila permohonan tidak ditanggapi atau ditolak tanpa alasan sah, pemohon berhak mengajukan surat keberatan kepada Atasan PPID dalam waktu 30 (tiga puluh) hari kerja.
TEXT,
            ],
            [
                'id' => 'SOP-012',
                'title' => 'SOP Verifikasi, Disposisi, dan Tindak Lanjut Pengaduan Masyarakat pada Portal BHIMA',
                'number' => 'SOP/BHIMA/012/2025',
                'year' => 2025,
                'topic' => 'Pelayanan Publik',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Penerimaan dan Nomor Tiket Pengaduan
Setiap laporan masyarakat yang masuk ke sistem BHIMA dicatat dengan nomor tiket unik dan berstatus awal Baru. Warga dapat memantau perkembangan laporan secara transparan.
Pasal 2 Ayat (1) Pemeriksaan Verifikator dan Permintaan Kelengkapan
Petugas Verifikator memeriksa kejelasan kronologi, lokasi, dan bukti pendukung dalam waktu 1 s.d. 2 hari kerja. Apabila data belum lengkap, status diubah menjadi Menunggu Kelengkapan disertai catatan hal yang perlu dilengkapi warga.
Pasal 3 Ayat (1) Disposisi ke Instansi Berwenang dan Penyelesaian
Laporan yang telah terverifikasi diubah menjadi Menunggu Disposisi untuk diteruskan ke perangkat daerah berwenang (Dalam Proses) hingga terdapat hasil penanganan nyata dan laporan dinyatakan Selesai.
TEXT,
            ],
            [
                'id' => 'SOP-013',
                'title' => 'SOP Penerbitan Surat Keterangan Tidak Mampu (SKTM) untuk Layanan Kesehatan dan Bantuan Hukum',
                'number' => 'SOP/DINSOS/013/2025',
                'year' => 2025,
                'topic' => 'Bantuan Hukum',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Persyaratan dan Proses Penerbitan SKTM
Warga pemohon SKTM membawa KTP-el dan Kartu Keluarga ke Kantor Desa/Kelurahan. Petugas melakukan verifikasi Data Terpadu Kesejahteraan Sosial (DTKS) atau surat pernyataan ketidakmampuan, dan menerbitkan SKTM pada hari kerja yang sama tanpa biaya (gratis).
TEXT,
            ],
            [
                'id' => 'SOP-014',
                'title' => 'SOP Pelayanan Ambulans Darurat dan Rujukan Terpadu Masyarakat',
                'number' => 'SOP/DINKES/014/2025',
                'year' => 2025,
                'topic' => 'Pelayanan Kesehatan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pemesanan Layanan Ambulans Gawat Darurat
Warga yang membutuhkan evakuasi medis darurat dapat menghubungi layanan gawat darurat Puskesmas/RSUD terdekat. Armada ambulans gawat darurat pemerintah daerah diberangkatkan bersama petugas medis pendamping tanpa pungutan biaya bahan bakar bagi peserta JKN/warga tidak mampu.
TEXT,
            ],
            [
                'id' => 'SOP-015',
                'title' => 'SOP Pengaduan dan Penanganan Pencemaran Lingkungan Hidup dan Limbah',
                'number' => 'SOP/DLH/015/2025',
                'year' => 2025,
                'topic' => 'Infrastruktur',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pelaporan Pencemaran Lingkungan
Warga yang terdampak pembuangan limbah cair, polusi udara, atau penumpukan sampah liar melaporkan lokasi, waktu kejadian, serta foto/video dampak pencemaran melalui Portal BHIMA atau Dinas Lingkungan Hidup untuk dilakukan verifikasi lapangan dan uji sampel dalam 3 (tiga) hari kerja.
TEXT,
            ],
            [
                'id' => 'SOP-016',
                'title' => 'SOP Pengaduan Konsumen dan Mediasi Badan Penyelesaian Sengketa Konsumen (BPSK)',
                'number' => 'SOP/DISDAG/016/2025',
                'year' => 2025,
                'topic' => 'Perlindungan Konsumen & Digital',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pengajuan Pengaduan Sengketa Konsumen
Konsumen yang menerima barang cacat, tidak sesuai spesifikasi, atau ditolak klaim garansinya oleh pelaku usaha dapat mengajukan permohonan penyelesaian sengketa ke sekretariat BPSK atau melalui rujukan BHIMA dengan melampirkan bukti pembelian/struk transaksi dan foto barang. Penyelesaian sengketa di BPSK dilakukan secara cepat dalam waktu maksimal 21 (dua puluh satu) hari kerja dan bebas biaya perkara.
TEXT,
            ],
            [
                'id' => 'SOP-017',
                'title' => 'SOP Pelayanan Perizinan Berusaha Mikro (NIB) Tanpa Biaya di DPMPTSP',
                'number' => 'SOP/DPMPTSP/017/2025',
                'year' => 2025,
                'topic' => 'Pelayanan Publik',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Pendampingan Penerbitan Nomor Induk Berusaha (NIB)
Pelaku Usaha Mikro dan Kecil (UMK) cukup membawa KTP-el, nomor telepon aktif, dan data jenis usaha ke gerai pelayanan DPMPTSP. Petugas mendampingi penerbitan NIB melalui sistem OSS secara gratis dalam waktu 1 (satu) hari kerja tanpa perantara calo.
TEXT,
            ],
            [
                'id' => 'SOP-018',
                'title' => 'SOP Pengurusan Surat Keterangan Ahli Waris di Desa/Kelurahan dan Kecamatan',
                'number' => 'SOP/PEMDES/018/2025',
                'year' => 2025,
                'topic' => 'Pertanahan',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Persyaratan Surat Keterangan Waris
Para ahli waris mengajukan permohonan dengan melampirkan Akta Kematian pewaris, Kartu Keluarga dan KTP seluruh ahli waris, buku nikah pewaris, serta surat pernyataan ahli waris yang ditandatangani bersama di hadapan dua orang saksi untuk diregistrasi oleh Lurah/Kepala Desa dan Camat dalam waktu paling lama 3 (tiga) hari kerja tanpa pungutan liar.
TEXT,
            ],
            [
                'id' => 'SOP-019',
                'title' => 'SOP Perlindungan Kerahasiaan Identitas Pelapor (Whistleblower) pada Layanan Publik',
                'number' => 'SOP/BHIMA/019/2025',
                'year' => 2025,
                'topic' => 'Dugaan Pungutan Liar',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Standar Pengamanan Identitas Pelapor
Setiap laporan pelanggaran disiplin aparatur, pungutan liar, dan penyimpangan anggaran yang masuk ke BHIMA menerapkan penyamaran data identitas pelapor dari pihak terlapor. Petugas verifikator dilarang membocorkan identitas pelapor kepada pihak mana pun di luar tim pemeriksa resmi.
TEXT,
            ],
            [
                'id' => 'SOP-020',
                'title' => 'SOP Maklumat Pelayanan dan Kompensasi Keterlambatan Layanan Administrasi Publik',
                'number' => 'SOP/ORGPEM/020/2025',
                'year' => 2025,
                'topic' => 'Pelayanan Publik',
                'body' => <<<'TEXT'
Pasal 1 Ayat (1) Hak Prioritas Layanan Atas Keterlambatan
Apabila penyelenggara layanan melampaui batas waktu standar pelayanan yang tercantum dalam maklumat pelayanan tanpa alasan sah, warga pemohon berhak mendapatkan penjelasan tertulis, antrean prioritas penyelesaian pada hari kerja berikutnya, dan pengantaran dokumen hasil layanan tanpa biaya tambahan.
TEXT,
            ],
        ];

        return array_map(fn (array $s) => [
            'id' => $s['id'],
            'title' => $s['title'],
            'type' => 'SOP Layanan',
            'number' => $s['number'],
            'year' => $s['year'],
            'category' => 'SOP',
            'topic' => $s['topic'],
            'status' => 'Terbit',
            'body' => $s['body'],
            'source_url' => '/rujukan/'.$s['id'],
            'updated' => $today,
        ], $sops);
    }
}

