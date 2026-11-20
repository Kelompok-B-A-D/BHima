# Panduan Pengambilan Data JDIH untuk Asisten Hukum Virtual BHIMA

Tujuan: setiap pertanyaan warga tentang **pasal yang dikenakan** dan **ancaman sanksi** dijawab dari teks aturan resmi yang benar, terkini, dan bisa ditelusuri. Tidak ada pasal atau angka sanksi yang berasal dari karangan.

## 1. Temuan Awal di Repo (Harus Diperbaiki Dulu)

| Temuan | Dampak pada akurasi |
|--------|---------------------|
| `JDIH-026` sampai `JDIH-080` (55 dari 80 dokumen JDIH) dibuat otomatis oleh perulangan di `LegalKnowledgeSeeder` dari 10 templat. Nomor, tahun, dan "Seri N" dikarang, isi pasalnya generik. | Asisten bisa mengutip peraturan yang tidak ada. Ini penyebab terbesar jawaban tidak akurat. |
| `JDIH-001` sampai `JDIH-025` berisi teks ringkasan buatan sendiri, bukan naskah resmi. | Bunyi pasal dan angka sanksi bisa meleset dari aslinya. |
| `source_url` berupa `/rujukan/JDIH-xxx` (halaman internal), bukan tautan ke sumber resmi. | Warga dan verifikator tidak bisa mengecek keaslian. |
| Tidak ada penanda status peraturan (berlaku, diubah, dicabut). | Sanksi lama bisa ditampilkan padahal sudah diganti. |

Aturan emas: **tidak ada dokumen masuk indeks tanpa naskah resmi dan tautan sumbernya.** Hapus dokumen `JDIH-026` sampai `JDIH-080` yang bersifat templat, lalu ganti dengan hasil pengambilan di bawah.

## 2. Perubahan Hukum Pidana yang Wajib Diperhitungkan

Per hasil penelusuran, rujukan sanksi pidana tidak boleh lagi mengandalkan KUHP lama.

- KUHP baru (UU No. 1 Tahun 2023) dan KUHAP baru (UU No. 20 Tahun 2025) sudah berlaku.
- UU No. 1 Tahun 2026 tentang Penyesuaian Pidana menyesuaikan ancaman pidana di banyak undang-undang. Cek setiap UU pada daftar di bagian 4 apakah pasal pidananya diubah olehnya.
- Pembahasan UU Ketenagakerjaan baru berlangsung sepanjang 2026, dan ada pemberitaan DPR telah mengesahkan UU Pelindungan Ketenagakerjaan. Nomor, tahun, dan tanggal berlakunya belum terverifikasi di sini, jadi **cek langsung di peraturan.bpk.go.id** sebelum menentukan apakah PP 35/2021 dan PP 36/2021 masih menjadi rujukan utama.
- UU Pelindungan Pekerja Rumah Tangga juga diberitakan telah disahkan. Verifikasi nomornya sebelum dimasukkan.

Sumber: [peraturan.bpk.go.id UU 1/2026](https://peraturan.bpk.go.id/Details/337869/uu-no-1-tahun-2026), [UU 1/2023 KUHP](https://peraturan.bpk.go.id/details/234935/uu-no-1-tahun-2023), [Antara tentang KUHAP baru](https://jatim.antaranews.com/berita/1019029/kuhap-baru-berlaku-atur-keadilan-restoratif-hingga-rekaman-cctv), [Bisnis tentang RUU Ketenagakerjaan](https://ekonomi.bisnis.com/read/20260722/12/1989612/pembahasan-ruu-ketenagakerjaan-masuki-babak-krusial).

## 3. Sumber Pengambilan Data (Urutan Prioritas)

| Prioritas | Sumber | Gunakan untuk |
|-----------|--------|---------------|
| 1 | [peraturan.bpk.go.id](https://peraturan.bpk.go.id) (halaman `Details/{id}` memuat status, riwayat perubahan, dan PDF) | UU, PP, Perpres, Permen. Sumber utama |
| 2 | [peraturan.go.id](https://peraturan.go.id) (berkas PDF, mis. `/files/uu-no-1-tahun-2026.pdf`) | Naskah PDF resmi |
| 3 | JDIH kementerian terkait (mis. `jdih.kemnaker.go.id`) | Permen dan peraturan teknis sektoral |
| 4 | JDIH pemerintah daerah | Perda dan Perbup/Perwali lokasi layanan BHIMA |
| 5 | Situs SOP instansi (Disdukcapil, Dinkes, Disnaker, BPN, UPTD PPA) | Dokumen SOP layanan |

Catatan teknis:
- Server aplikasi dan beberapa situs menolak akses otomatis (robots). Unduh PDF secara manual atau lewat cara yang diizinkan situsnya, lalu simpan di `storage/app/private/jdih/`. Jangan menembus pembatasan situs.
- Simpan **PDF asli** dan **URL halaman rincinya** untuk setiap dokumen.

## 4. Cakupan Kasus dan Kandidat Peraturan

Cakupan mengikuti kategori yang sudah ada di BHIMA dan kamus ungkapan warga di `LegalRetrievalService::COLLOQUIAL_EXPANSIONS`. Semua entri adalah **kandidat**: verifikasi nomor, judul, status berlaku, dan perubahannya di peraturan.bpk.go.id sebelum dimasukkan.

| Topik (kategori BHIMA) | Contoh kasus warga | Kandidat peraturan |
|------------------------|--------------------|--------------------|
| Administrasi Kependudukan | KTP-el, KK, akta, pungutan saat mengurus | UU 23/2006 jo. UU 24/2013, Perpres 96/2018 |
| Pelayanan Publik | Layanan lambat, ditolak tanpa alasan | UU 25/2009, UU 30/2014 (Administrasi Pemerintahan), UU 37/2008 (Ombudsman) |
| Dugaan Pungli dan Suap | "Uang rokok", calo, uang pelicin | UU 31/1999 jo. UU 20/2001 (Tipikor), Perpres 87/2016 (Saber Pungli), KUHP baru, PP 94/2021 |
| Pelayanan Kesehatan | IGD menolak pasien, uang muka, BPJS nonaktif | UU 17/2023, Perpres 82/2018 (JKN) |
| Infrastruktur | Jalan rusak tanpa rambu, kecelakaan | UU 22/2009, UU 2/2022 (Jalan) |
| Ketenagakerjaan | PHK sepihak, upah terlambat, ijazah ditahan | UU 13/2003, UU 6/2023, PP 35/2021, PP 36/2021, UU Pelindungan Ketenagakerjaan (jika sudah terbit) |
| Pertanahan | Patok digeser, sertifikat hilang, serobot tanah | UUPA 5/1960, PP 24/1997, Permen ATR/BPN 21/2020, KUHP baru |
| Konsumen dan Digital | Pinjol, debt collector, data pribadi bocor, barang cacat | UU 8/1999, UU 27/2022 (PDP), UU ITE dan perubahannya, POJK 22/2023 |
| Perempuan dan Anak | KDRT, kekerasan seksual, perlindungan anak | UU 23/2004, UU 12/2022 (TPKS), UU 35/2014, UU 31/2014 jo. perubahan |
| Keterbukaan Informasi dan Anggaran | Dana desa, APBDes, bantuan sosial | UU 14/2008, UU 6/2014 (Desa), Permendagri pengelolaan keuangan desa |
| Lingkungan | Pencemaran, limbah | UU 32/2009 |
| Bantuan Hukum | Tidak mampu bayar pengacara | UU 16/2011, PP 42/2013 |
| Pidana umum dan acara | Penipuan, penganiayaan, pencurian, lapor polisi | KUHP baru (UU 1/2023), UU 1/2026, KUHAP baru (UU 20/2025) |
| Pelindungan Pekerja Rumah Tangga dan Migran | Majikan menahan gaji, TKI | UU PPRT (verifikasi nomor), UU 18/2017 |

Tambahkan topik baru dengan cara yang sama: tulis kasus warga, petakan ke peraturan, ambil naskah resminya.

## 5. Alur Pengambilan per Dokumen

```
1. Daftarkan di manifest (jdih_manifest.csv)
2. Unduh PDF resmi + catat URL halaman rinci
3. Verifikasi status: berlaku / diubah oleh / dicabut oleh
4. Ekstrak teks (jangan retype manual), cek hasil per halaman
5. Pecah per Pasal dan Ayat (bukan per paragraf acak)
6. Beri metadata dan tandai pasal ketentuan pidana
7. Hubungkan pasal norma ke pasal sanksinya
8. Review manusia (dua orang) terhadap 100% pasal sanksi
9. Ingest ke knowledge_chunks, jalankan uji akurasi
```

### Manifest `jdih_manifest.csv`

```
doc_key,jenis,nomor,tahun,judul,kategori,status,diubah_oleh,dicabut_oleh,url_halaman,url_pdf,tanggal_verifikasi,verifikator
JDIH-001,UU,24,2013,Perubahan atas UU 23/2006 ...,Administrasi Kependudukan,berlaku,UU 6/2023?,,https://peraturan.bpk.go.id/Details/...,https://...pdf,2026-11-xx,nama
```

Kolom `status` dan `diubah_oleh` wajib diisi. Dokumen berstatus `dicabut` tidak diindeks untuk jawaban, tetapi boleh disimpan sebagai arsip agar sistem bisa menjelaskan "aturan ini sudah diganti".

## 6. Struktur Data per Potongan (Chunk)

Tambahkan kolom pada `knowledge_chunks` (migrasi baru) agar jawaban sanksi presisi:

| Kolom baru | Isi |
|------------|-----|
| `pasal_nomor` | Angka pasal, mis. `95B` |
| `ayat` | Nomor ayat bila ada |
| `is_ketentuan_pidana` | `true` bila pasal memuat ancaman sanksi |
| `sanksi_jenis` | `pidana` / `administratif` / `denda` / `lainnya` |
| `sanksi_teks` | Bunyi sanksi disalin persis dari naskah |
| `rujuk_pasal` | Pasal norma yang diancam, mis. `Pasal 79A` |
| `status_berlaku` | `berlaku` / `diubah` / `dicabut` |
| `source_page_url` | Tautan halaman resmi |
| `pdf_page` | Halaman PDF tempat pasal berada |

Aturan penting: pasal norma ("dilarang melakukan X") dan pasal sanksinya ("setiap orang yang melanggar Pasal X dipidana ...") sering berada di bab berbeda. Isi `rujuk_pasal` agar saat pasal norma ditemukan, pasal sanksinya ikut terambil.

## 7. Aturan Retrieval untuk Pertanyaan Sanksi dan Pasal

Terapkan di `LegalRetrievalService` dan `LegalAnalysisService`:

1. Bila pertanyaan memuat kata seperti "sanksi", "hukuman", "dipidana", "denda", "pasal berapa", prioritaskan chunk dengan `is_ketentuan_pidana = true`.
2. Setiap pasal norma yang ditemukan otomatis menarik pasal sanksinya lewat `rujuk_pasal`.
3. Angka sanksi pada jawaban **harus identik** dengan `sanksi_teks`. Validasi sudah ada (`isSanctionGroundedInChunk`), pertahankan.
4. Bila beberapa aturan berlaku pada kasus yang sama (misalnya UU sektoral dan KUHP baru), tampilkan keduanya dan jelaskan mana yang khusus.
5. Bila chunk berstatus `diubah`, tampilkan versi terbaru dan beri catatan "diubah oleh ...".
6. Bila tidak ada pasal sanksi di konteks, jawab `Tidak tercantum dalam potongan pasal yang ditemukan`. Dilarang menebak.
7. Selalu tampilkan nama peraturan, nomor, tahun, pasal, dan tautan sumber resmi pada setiap klaim.

## 8. Rencana Kerja

| No | Tugas | Keluaran |
|----|-------|----------|
| 1 | Hapus dokumen templat `JDIH-026..080` dari seeder | Tidak ada dokumen palsu |
| 2 | Susun `jdih_manifest.csv` dari tabel bagian 4 dan verifikasi status tiap dokumen | Manifest terverifikasi |
| 3 | Unduh PDF resmi ke `storage/app/private/jdih/` | Arsip naskah |
| 4 | Migrasi kolom baru bagian 6 | Skema siap |
| 5 | Ubah `LegalIngestionService`: baca dari PDF dan manifest, pecah per pasal, isi metadata | Ingest dari sumber resmi |
| 6 | Penandaan pasal sanksi dan `rujuk_pasal` (otomatis dengan pola "dipidana", "denda", lalu dicek manual) | Chunk sanksi lengkap |
| 7 | Review dua orang atas semua pasal sanksi | Tanda `terverifikasi` |
| 8 | Ubah retrieval sesuai bagian 7 | Jawaban sanksi presisi |
| 9 | Buat set uji (bagian 9) dan jalankan `php artisan test` | Laporan akurasi |
| 10 | Jadwalkan pengecekan ulang status peraturan setiap bulan | Data tidak basi |

## 9. Uji Akurasi

Buat minimal 50 pertanyaan uji, 3 sampai 5 per topik, dengan jawaban acuan dari naskah resmi.

Contoh format (`tests/fixtures/akurasi_sanksi.json`):

```json
{
  "pertanyaan": "Petugas Disdukcapil minta uang untuk bikin KTP-el, pasal apa dan sanksinya?",
  "pasal_diharapkan": ["Pasal 79A", "Pasal 95B"],
  "dokumen": "UU 24/2013",
  "sanksi_harus_memuat": "<disalin persis dari naskah resmi>"
}
```

Lulus bila:
- Pasal yang disebut ada pada naskah resmi dan relevan (target 100%, tanpa pasal fiktif).
- Angka sanksi identik dengan naskah (target 100%).
- Tidak ada dokumen berstatus `dicabut` dipakai sebagai rujukan utama.
- Pertanyaan di luar cakupan dijawab dengan pesan "belum ditemukan" dan arahan eskalasi.
- Tidak ada vonis atau prediksi menang atau kalah.

## 10. Batasan yang Harus Tetap Ditampilkan

- Jawaban adalah informasi awal berdasarkan teks aturan, bukan nasihat hukum dan bukan putusan.
- "Pasal yang dikenakan" ditulis sebagai "pasal yang berpotensi terkait" karena penilaian fakta menjadi kewenangan aparat penegak hukum.
- Kasus di luar cakupan diarahkan ke verifikator BHIMA, Pos Bantuan Hukum, atau tombol Ajukan Pengaduan.
