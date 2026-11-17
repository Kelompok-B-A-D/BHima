# SCRUM-5 & SCRUM-6

## Audit awal

- Branch awal `Agmar_branch`, working tree bersih. Laravel 12, PHP requirement ^8.2 (CLI 8.4.14), Composer, npm/package-lock, Blade, Eloquent/Query Builder, Tailwind 4, Vite 7.
- Sumber repository: `docs/DASHBOARD.md`, routes, middleware, controller, migrasi, UI dan tes dashboard. Proposal lengkap tidak ditemukan dalam repository; kebutuhan proposal menggunakan lampiran tugas.
- Database yang didukung mengikuti konfigurasi Laravel; contoh default SQLite, bukan bukti bahwa lingkungan operasional memakai MySQL. Relasi terverifikasi dari migrasi: dashboard_records.assigned_to dan dashboard_activities.user_id ke users. Tidak mengakses/mengubah database operasional.
- `dashboard_records`, collection `contents`, sudah menyimpan title/category/body/status/updated dalam JSON. Category lama adalah jenis konten (SOP/Panduan/Pengumuman/Informasi), sehingga dipertahankan; kategori layanan ditambahkan sebagai `service_category` dalam payload yang sama.
- Autentikasi sesi serta DashboardAccess tersedia; hanya admin/verifikator, tanpa peran atau relasi unit perangkat daerah. Admin mengelola konten; verifikator tidak memperoleh izin tambahan.
- UI dashboard memiliki warna navy/biru, panel, formulir, tabel, sidebar/drawer. Penyimpanan lokal privat sudah tersedia; upload awal hanya untuk JDIH. Tidak mengubah modul JDIH.
- Belum tersedia katalog publik, detail, filter database, metadata layanan lengkap, dan upload PDF layanan. Pencarian publik belum memiliki analytics; tidak menambah analytics.
- Baseline `php artisan test`: 1 lulus, 10 gagal karena MissingAppKeyException. Baseline build lulus. `.env` tidak diubah; tes berikut memakai APP_KEY acak hanya pada proses.

## Implementasi dan file

- `app/Models/ServiceContent.php`: model Eloquent pada tabel existing, scope collection contents dan publikasi Terbit.
- `app/Http/Controllers/ServiceContentController.php`: daftar publik/admin dengan pagination 12, pencarian backend, filter kategori layanan/instansi/jenis/status admin, detail, CRUD, upload privat, audit, unduhan terotorisasi.
- `app/Http/Controllers/DashboardDataController.php`: endpoint hapus contents lama memakai alur penghapusan yang sama, termasuk pembersihan PDF; kontrak respons JSON tetap.
- `resources/views/services/{layout,index,form,show,unavailable}.blade.php`: navigasi BHIMA, tabel admin, kartu katalog, form validasi, detail dan pesan dokumen tidak tersedia.
- `resources/js/services.js`: konfirmasi hapus, validasi ekstensi/ukuran browser, nama file terpilih, status submit, drawer mobile.
- `resources/css/app.css`: gaya modul dan breakpoint responsive, memakai token/komponen existing.
- `resources/views/welcome.blade.php`: tautan katalog dari beranda.
- `routes/web.php`: route modul sebelum route dashboard generik.
- `vite.config.js`: entry JavaScript modul; tanpa dependency baru.
- `tests/Feature/ServiceContentTest.php`: tujuh tes fitur baru, menggunakan SQLite in-memory dan filesystem fake.

## Data dan endpoint

Tidak ada tabel baru, migrasi, backfill, atau perubahan `.env`. Payload existing diperluas dengan service_category, agency, type (daerah/umum), requirements, procedure, duration, cost, filename. Metadata file_path/timestamps tetap memakai kolom existing. Konten lama tetap terbaca; metadata yang belum diisi ditampilkan sebagai belum tersedia. Tidak memasukkan data layanan resmi atau seed produksi.

| Method | Path | Akses |
| --- | --- | --- |
| GET | /layanan | Publik, hanya Terbit |
| GET | /layanan/{key} | Publik, hanya Terbit |
| GET | /layanan/{key}/dokumen | Terbit publik; Draf hanya admin aktif |
| GET | /admin/konten | Admin aktif |
| GET | /admin/konten/tambah | Admin aktif |
| POST | /admin/konten | Admin aktif, CSRF |
| GET | /admin/konten/{key}/edit | Admin aktif |
| PUT | /admin/konten/{key} | Admin aktif, CSRF |
| DELETE | /admin/konten/{key} | Admin aktif, CSRF |

Dokumen PDF opsional, maksimal 10 MB; backend memvalidasi ekstensi, MIME, dan ukuran. File disimpan bernama acak di disk local privat direktori services. Nama download berasal dari judul layanan. Path tidak dikirim ke UI. Tidak ada unduhan placeholder. Dokumen lama dibersihkan setelah transaksi perubahan berhasil dan tidak lagi direferensikan; upload baru dibersihkan jika transaksi gagal. Kegagalan simpan menampilkan pesan dan mempertahankan isian; berkas harus dipilih kembali. Kegagalan pembersihan dilaporkan ke log, data aktif tetap konsisten.

## Verifikasi

- `APP_KEY=base64:$(openssl rand -base64 32) vendor/bin/phpunit`: 18 tes, 190 assertion, lulus. Kunci hanya untuk tes; tidak disimpan.
- `vendor/bin/pint --test`: lulus.
- `npm run build`: lulus.
- `node --check resources/js/services.js`: lulus.
- `git diff --check`: lulus.
- `php artisan route:list --path=layanan` dan `--path=admin/konten`: 3 route publik dan 6 route pengelolaan terdaftar.
- Cakupan backend: CRUD/audit, validasi wajib, file PDF palsu/ekstensi/ukuran salah, penggantian/penghapusan file, rollback kegagalan simpan, mempertahankan file saat tanpa upload, publikasi/unpublish, kombinasi filter/pencarian, reset, pagination, escaping, akses tamu/verifikator/admin nonaktif, isolasi collection JDIH, missing file, dan isi binary unduhan.
- Tes dashboard existing tetap lulus. `php artisan test` dengan kunci sementara memunculkan warning dari `.env` yang tidak ada; PHPUnit langsung lulus tanpa warning tersebut.

## Batas dan operasional

- Hak akses perangkat daerah per unit belum bisa diterapkan karena konsep peran/unit tersebut tidak tersedia. Tidak memperluas RBAC.
- Tes database berjalan pada SQLite; integrasi MySQL belum dijalankan.
- Tidak melakukan deployment, push, perubahan infrastruktur, reset database, atau pemasangan dependency.
- Environment aplikasi masih membutuhkan konfigurasi APP_KEY/database yang sah oleh pengelola agar dapat dijalankan secara operasional. Perubahan ini tidak membuat atau mengubah `.env`.

## Pemeriksaan browser

Chrome headless lokal melalui Chrome DevTools Protocol, tanpa dependency baru. Server pengujian memakai SQLite di `/private/tmp/bhima-browser-DoTok6`, seluruh konten berlabel dummy. Sepuluh pemeriksaan lulus: hanya konten Terbit di katalog, tidak ada overflow horizontal pada katalog mobile 390×844, kartu satu kolom, empty state pencarian, detail/missing document, admin melihat Draf/Terbit, drawer mobile, form mobile tanpa overflow, validasi kolom wajib browser, dan pembuatan konten melalui browser dengan notifikasi sukses. Screenshot desktop 1440×1000 dan mobile juga diperiksa visual. Berkas bukti lokal: `desktop.png`, `mobile.png`, `mobile-form.png` di direktori sementara tersebut.

Status: SCRUM-5 dan SCRUM-6 memenuhi alur fungsional dan verifikasi pada stack lokal yang tersedia. Otorisasi perangkat daerah per unit tidak ditambahkan karena belum didukung sistem; validasi MySQL tetap belum dijalankan.
