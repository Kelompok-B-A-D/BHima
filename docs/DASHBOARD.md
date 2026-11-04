# Dashboard internal BHIMA

## Dasar implementasi

Codebase sumber: Kelompok-B-A-D/BHima, main, commit 958e636992e381f00d9748dda8a130f5448ddc87. Workspace semula kosong dan diisi arsip source repository tersebut; riwayat Git kemudian dihubungkan oleh pengguna.

Stack: Laravel 12, PHP >=8.2, Blade, JavaScript ES modules, Tailwind CSS 4, Vite 7, session authentication Laravel, database via Query Builder/Eloquent. Tidak menambah dependency aplikasi. package-lock.json mengunci dependency frontend existing.

Repository awal hanya skeleton Laravel dan halaman welcome, tanpa endpoint operasional, login/logout, role, atau dashboard. Referensi Stitch tidak dapat diakses. Proposal DOCX dibaca sebagai kebutuhan proyek. Penambahan autentikasi internal dan migration disetujui pengguna. Grafik menggunakan SVG dan meter native, tanpa memasang library chart baru; angka grafik berasal dari data aktual atau fixtures berlabel demo.

## Menjalankan

Jika environment belum disiapkan, lakukan sekali:

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan bhima:create-user admin@bhima.id --role=admin
npm run build
php artisan serve
```

Jangan menimpa `.env` yang sudah ada. SQLite adalah default contoh Laravel; aplikasi juga memakai koneksi database Laravel yang dikonfigurasi pengguna. Password akun diminta secara tersembunyi lewat CLI, tanpa password default.

- Login: `/login`
- Admin: `/admin/dashboard`
- Verifikator: `/verifikator/dashboard`
- Demo lokal: `/demo/admin/dashboard` dan `/demo/verifikator/dashboard`

Demo hanya tersedia di environment local/testing. Data demo terpisah dari database, tersimpan di sessionStorage browser dan seluruh halaman berlabel demonstrasi. Demo bukan autentikasi atau bukti keamanan; RBAC operasional diperiksa di server.

## Halaman dan interaksi

Dashboard admin/verifikator, pengaduan, detail dengan tabs, verifikasi, permintaan kelengkapan, penolakan, disposisi, status tindak lanjut, catatan internal, penugasan petugas oleh admin; kelola konten, JDIH, anggaran, akun, matriks peran, statistik pengaduan, tren pencarian, kesiapan basis AI, log aktivitas, preferensi jumlah baris.

Sidebar menyesuaikan peran; navigasi mobile berupa drawer. Daftar mendukung pencarian/filter/paginasi; pengaduan memiliki filter kategori/status/tanggal. Form memakai validasi browser dan server, konfirmasi, status proses, pesan kesalahan, dan toast. Grafik memiliki label, nilai, legenda, serta tooltip native. Preferensi jumlah baris hanya berlaku selama halaman pengaturan dibuka, belum disimpan lintas navigasi.

## Backend

- `InternalAuthController`: login/logout, rotasi sesi, pembatasan percobaan.
- `DashboardAccess`: user aktif dan role yang benar; URL admin ditolak untuk verifikator.
- `DashboardDataController`: endpoint data same-origin ber-CSRF, validasi, transaksi dan audit log. Verifikator hanya dapat membaca/mengubah pengaduan yang ditugaskan kepadanya; payload perubahan tidak dapat mengganti kronologi/identitas.
- Migration menambahkan role/is_active dan tabel dashboard_records (collection + JSON payload, assignment, lokasi berkas privat) serta dashboard_activities. Struktur ini fondasi modul dashboard, belum model domain penuh untuk portal publik.
- Akun menggunakan tabel users Laravel, password hash, dan penolakan pencabutan akses admin sendiri.
- Upload JDIH memvalidasi PDF/10 MB, membatasi kapasitas 100, menyimpan berkas privat, dan mengunduh melalui endpoint admin. Tidak menandai ekstraksi/indeksasi berhasil secara otomatis.
- Dokumen yang diganti/dihapus tidak langsung menghapus berkas fisik lama; perlu kebijakan retensi/cleanup terpisah.

## Batas implementasi yang masih terbuka

Pekerjaan belum memenuhi seluruh acceptance criteria produksi:

1. Modul pengaduan warga belum ada di repository. Belum ada alur intake publik yang memasukkan laporan/bukti ke dashboard_records; produksi tidak diisi fixtures. Struktur payload contoh ada di resources/js/dashboard/data.js dan perlu adapter intake domain tervalidasi ketika modul publik dibangun.
2. Lampiran bukti pengaduan belum terintegrasi; panel menampilkan keadaan belum tersedia.
3. Ekstraksi PDF, chunking, indeks RAG, DeepSeek, dan proses ulang dokumen gagal belum tersedia. Upload hanya menyimpan dokumen dan metadata dengan status Menunggu.
4. Pencatatan kueri pencarian publik belum tersedia. Tren pencarian produksi kosong, demo menggunakan contoh agregat.
5. Grafik anggaran menampilkan realisasi; perbandingan pagu/realisasi tersedia pada tabel. Filter periode pengaduan tersedia pada halaman statistik; filter tahun anggaran melalui pencarian tabel.
6. Kelola hak akses menggunakan dua peran tetap admin/verifikator. Belum ada editor permission granular atau peran perangkat daerah.
7. Detail pengaduan dibuka sebagai dialog, belum URL detail mandiri. Ekspor data belum tersedia pada sistem awal dan belum ditambahkan.
8. Referensi Stitch dan validasi visual browser belum berhasil diperiksa. Responsivitas diimplementasikan melalui breakpoint CSS, belum diuji lintas perangkat secara visual.

## Validasi

- `npm run build`: berhasil (Vite production build).
- `php artisan test`: 11 tests, 52 assertions lulus pada SQLite in-memory. Meliputi guest/inactive RBAC, akses URL terlarang, isolasi assignment, transisi disposisi, CRUD/audit, validasi anggaran, self-revocation, upload/kapasitas JDIH, login/logout, demo nonproduksi.
- `vendor/bin/pint --test`: jalankan setelah formatter.
- Tidak ada script lint JavaScript yang disediakan codebase awal.

## File implementasi

- app/Console/Commands/CreateInternalUser.php
- app/Http/Controllers/DashboardDataController.php
- app/Http/Controllers/InternalAuthController.php
- app/Http/Middleware/DashboardAccess.php
- database/migrations/2026_11_03_000001_add_internal_dashboard.php
- resources/views/dashboard/index.blade.php
- resources/views/dashboard/login.blade.php
- resources/js/dashboard/{app,data,service,ui}.js
- resources/js/app.js
- resources/css/app.css
- routes/web.php
- tests/Feature/DashboardTest.php
- package-lock.json
- docs/DASHBOARD.md

Environment, vendor, node_modules, hasil build, dan data lokal tidak dimasukkan ke Git. Tidak ada deployment atau push.
