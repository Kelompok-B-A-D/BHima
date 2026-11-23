# BHIMA — Asisten Hukum Virtual (RAG AI) & Portal Layanan Masyarakat

Platform **BHIMA (Bantuan Hukum dan Informasi Masyarakat)** menyediakan antarmuka konsultasi hukum publik dan pencarian konteks hukum (*Retrieval-Augmented Generation* / RAG) yang mencocokkan kronologi permasalahan warga terhadap indeks **100 dokumen JDIH dan SOP Layanan**, serta dashboard internal untuk verifikator dan administrator.

## 1. Cara Setup Lokal

Jalankan perintah berikut pada direktori repositori:

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Untuk membangun aset antarmuka (Vite + Tailwind CSS 4):

```sh
npm run build
```

## 2. Cara Menjalankan Ingestion & Indeks 100 Dokumen (Idempotent)

Untuk memuat korpus 100 dokumen (80 peraturan JDIH + 20 SOP Layanan), mengekstrak teks, memecah per Pasal/Ayat (fallback per paragraf dengan *overlap*), serta membuat vektor *embedding* ke tabel `knowledge_chunks`:

```sh
php artisan bhima:ingest-knowledge --seed
```

Perintah ini bersifat **idempotent**: dapat dijalankan berulang kali (`php artisan bhima:ingest-knowledge`) tanpa menggandakan dokumen maupun potongan pasal di basis data. Anda juga dapat menjalankan `php artisan migrate --seed`.

## 3. Variabel `.env` yang Dibutuhkan

Salin dari `.env.example`. Konfigurasi utama fitur Asisten Hukum Virtual (RAG):

```dotenv
# Zona Waktu Aplikasi
APP_TIMEZONE=Asia/Jakarta

# Parameter Retrieval
RAG_TOP_K=5
RAG_MIN_SIMILARITY=0.26
RAG_SEMANTIC_WEIGHT=0.60
RAG_KEYWORD_WEIGHT=0.40
RAG_CHUNK_MAX_CHARS=600
RAG_CHUNK_OVERLAP_CHARS=120

# Provider Embedding: 'local' (default, ringan di aplikasi) atau 'api' (OpenAI-compatible)
RAG_EMBEDDING_PROVIDER=local
RAG_EMBEDDING_BASE_URL=https://api.openai.com/v1
RAG_EMBEDDING_API_KEY=
RAG_EMBEDDING_MODEL=bhima-legal-embed-v1
RAG_EMBEDDING_DIMENSIONS=128
RAG_EMBEDDING_TIMEOUT=10

# Provider LLM Generasi Jawaban: 'local_grounded' (default) atau 'api' (endpoint paas.id / OpenAI-compatible)
# Samakan endpoint, model, dan key dengan DEEPSEEK_* agar tidak ada dua tagihan terpisah
RAG_LLM_PROVIDER=local_grounded
RAG_LLM_BASE_URL=https://ai.paas.id
RAG_LLM_API_KEY=
RAG_LLM_MODEL=deepseek-v4.1-flash
RAG_LLM_TEMPERATURE=0.1
RAG_LLM_MAX_TOKENS=1200
RAG_LLM_TIMEOUT=20

# Keamanan & Pembatasan Konsultasi
RAG_MIN_CHRONOLOGY_LENGTH=10
RAG_MAX_CHRONOLOGY_LENGTH=4000
RAG_RATE_LIMIT_PER_MINUTE=15

# Analisis Pasal, Estimasi Ancaman Sanksi & Eskalasi Pengaduan (PBI 2: mock | deepseek)
LLM_PROVIDER=mock
DEEPSEEK_API_KEY=
DEEPSEEK_BASE_URL=https://ai.paas.id
DEEPSEEK_MODEL=deepseek-v4.1-flash
DEEPSEEK_SEND_RESPONSE_FORMAT=true
DEEPSEEK_TIMEOUT=20
DEEPSEEK_TEMPERATURE=0.1
DEEPSEEK_MAX_TOKENS=1500
ANALYSIS_RATE_LIMIT_PER_MINUTE=15

# Kuota Token Harian & Circuit Breaker LLM (PBI 3)
LLM_DAILY_TOKEN_LIMIT=200000
LLM_DAILY_REQUEST_LIMIT=500
LLM_PER_REQUEST_TOKEN_CAP=3000
LLM_CIRCUIT_FAILURE_THRESHOLD=3
LLM_CIRCUIT_COOLDOWN_MINUTES=5
```

System prompt asisten disimpan secara terpisah di [`config/rag.php`](config/rag.php), system prompt dan aturan analisis pasal terstruktur (PBI 2) disimpan di [`config/legal_analysis.php`](config/legal_analysis.php), sedangkan batas kuota token harian dan circuit breaker (PBI 3) disimpan di [`config/llm_quota.php`](config/llm_quota.php).

### Cara Beralih dari `MockProvider` ke `DeepSeekProvider` (Gateway `paas.id`)

Secara default, aplikasi berjalan menggunakan `LLM_PROVIDER=mock` (`MockProvider`) dan `RAG_LLM_PROVIDER=local_grounded` yang menyusun jawaban serta analisis pasal langsung dari potongan teks *chunk* hasil *retrieval* tanpa memanggil jaringan eksternal.

Untuk beralih ke **DeepSeek API melalui gateway `paas.id`** saat kunci API sudah tersedia:
1. Buka berkas `.env` lokal (jangan pernah di-commit ke repositori) dan isi variabel berikut:
   ```dotenv
   APP_TIMEZONE=Asia/Jakarta

   LLM_PROVIDER=deepseek
   DEEPSEEK_API_KEY=isi-kunci-api-lokal-anda
   DEEPSEEK_BASE_URL=https://ai.paas.id
   DEEPSEEK_MODEL=deepseek-v4.1-flash
   DEEPSEEK_SEND_RESPONSE_FORMAT=true

   RAG_LLM_PROVIDER=api
   RAG_LLM_BASE_URL=https://ai.paas.id
   RAG_LLM_API_KEY=isi-kunci-api-lokal-anda
   RAG_LLM_MODEL=deepseek-v4.1-flash
   ```
   Jika `RAG_LLM_API_KEY` dibiarkan kosong, `config/rag.php` otomatis mewarisi `DEEPSEEK_API_KEY`. Bila gateway menolak parameter `response_format: json_object`, `DeepSeekProvider` otomatis mengulang tanpa field tersebut dan mem-parsing JSON melalui `parseJsonContent()` (atau set `DEEPSEEK_SEND_RESPONSE_FORMAT=false`).
2. Bersihkan cache konfigurasi bila aktif:
   ```sh
   php artisan config:clear
   ```
3. Apabila `LLM_PROVIDER=deepseek` diaktifkan tetapi `DEEPSEEK_API_KEY` masih kosong, aplikasi tidak akan *crash* dan tidak memanggil jaringan, melainkan mencatat log peringatan dan menampilkan pesan ramah disertai tombol eskalasi pengaduan.

## 4. Skenario Uji PBI 2 & Hasil yang Diharapkan

Berikut adalah 5 skenario uji utama (beserta skenario uji *guardrail*) yang dijalankan otomatis melalui `LegalAnalysisPbi2Test`:

1. **Skenario 1 — Pungutan Liar Pengurusan KTP-el**:
   - *Input Kronologi*: `"Saya mengurus KTP elektronik yang hilang di kantor Disdukcapil, tetapi oknum petugas meminta uang rokok Rp100.000 agar cepat selesai."`
   - *Hasil yang Diharapkan*: Menampilkan rujukan **UU No. 24 Tahun 2013** & **Perpres No. 87 Tahun 2016**, pasal berpotensi terkait (**Pasal 79A** tentang layanan dokumen kependudukan gratis & **Pasal 95B**), estimasi ancaman sanksi pidana eksplisit dari teks Pasal 95B (*pidana penjara paling lama 6 tahun dan/atau denda paling banyak Rp75.000.000,00*), serta `perlu_eskalasi: true`.
2. **Skenario 2 — Penolakan Pasien Gawat Darurat IGD & Permintaan Uang Muka**:
   - *Input Kronologi*: `"Pasien kecelakaan gawat darurat dibawa ke IGD rumah sakit tetapi ditolak dan diminta uang muka deposit Rp5 juta sebelum ditangani dokter."`
   - *Hasil yang Diharapkan*: Menampilkan rujukan **UU No. 17 Tahun 2023** & **Perpres No. 82 Tahun 2018**, pasal berpotensi terkait (**Pasal 174 Ayat (1)-(2)** & **Pasal 438 Ayat (1)**), serta estimasi ancaman sanksi pidana dari Pasal 438 Ayat (1) (*pidana penjara paling lama 2 tahun atau pidana denda paling banyak Rp200.000.000,00*).
3. **Skenario 3 — Jalan Kabupaten Rusak Berlubang Tanpa Rambu & Kecelakaan**:
   - *Input Kronologi*: `"Jalan kabupaten di desa kami rusak berlubang parah tanpa rambu peringatan sehingga warga jatuh kecelakaan lalu lintas."`
   - *Hasil yang Diharapkan*: Menampilkan rujukan **UU No. 22 Tahun 2009** & **UU No. 2 Tahun 2022**, pasal berpotensi terkait (**Pasal 24 Ayat (1)-(2)** & **Pasal 273 Ayat (1)**), serta estimasi ancaman sanksi pidana dari Pasal 273 Ayat (1) (*penjara paling lama 6 bulan atau denda paling banyak Rp12.000.000,00*).
4. **Skenario 4 — PHK Sepihak, Upah Terlambat & Penahanan Ijazah Asli**:
   - *Input Kronologi*: `"Perusahaan melakukan PHK sepihak tanpa perundingan bipartit, gaji bulan terakhir belum dibayar, dan ijazah asli saya ditahan."`
   - *Hasil yang Diharapkan*: Menampilkan rujukan **PP No. 35 Tahun 2021**, **PP No. 36 Tahun 2021**, dan **Perda No. 4 Tahun 2022**, pasal berpotensi terkait (**Pasal 19 Ayat (1)-(2)**, **Pasal 61 Ayat (1)**), estimasi sanksi administratif denda keterlambatan upah pada Pasal 61 Ayat (1), serta keterangan `"Tidak tercantum dalam potongan pasal yang ditemukan"` pada pasal yang tidak memuat rumusan sanksi eksplisit.
5. **Skenario 5 — Sengketa Patok Batas Tanah Pekarangan Bersertifikat SHM**:
   - *Input Kronologi*: `"Tetangga menggeser patok batas bidang tanah pekarangan keluarga kami secara sepihak padahal kami memegang sertifikat hak milik (SHM)."`
   - *Hasil yang Diharapkan*: Menampilkan rujukan **PP No. 24 Tahun 1997** & **Permen ATR/BPN No. 21 Tahun 2020** (**Pasal 18 Ayat (1)**, **Pasal 6 Ayat (1)**, **Pasal 34 Ayat (1)**), keterangan `"Tidak tercantum dalam potongan pasal yang ditemukan"` pada pasal prosedur mediasi tanpa mengarang angka sanksi, serta tombol **Ajukan Pengaduan** yang mengisi otomatis formulir `/pengaduan/buat`.

## 5. Kuota Token Harian, Circuit Breaker & Fallback Katalog JDIH (PBI 3)

Seluruh pemanggilan LLM eksternal (`LegalAssistantService` pada `RAG_LLM_PROVIDER=api` dan `DeepSeekProvider` pada `LLM_PROVIDER=deepseek`) menggunakan **satu penghitung kuota harian bersama** (`LlmQuotaService`) pada tabel `llm_daily_usage` dan `llm_usage_logs` dengan reset otomatis setiap pergantian hari pada zona waktu `Asia/Jakarta`.

- **Pembatasan Sebelum Panggilan Jaringan**: Apabila circuit breaker sedang terbuka (`circuit_open`), proyeksi token (`tokens_used + LLM_PER_REQUEST_TOKEN_CAP`) melebihi `LLM_DAILY_TOKEN_LIMIT`, atau jumlah permintaan mencapai `LLM_DAILY_REQUEST_LIMIT`, sistem tidak mengirim permintaan HTTP eksternal dan langsung mengembalikan rekomendasi pasal dari katalog JDIH (`CatalogFallbackService`).
- **Circuit Breaker Otomatis**: Kegagalan beruntun (`429`, `5xx`, `timeout`, atau `invalid_json`) sebanyak `LLM_CIRCUIT_FAILURE_THRESHOLD` kali membuka circuit breaker selama `LLM_CIRCUIT_COOLDOWN_MINUTES` menit dan otomatis pulih kembali setelah masa pendinginan selesai.
- **Kepatuhan Guardrail & Eskalasi**: Hasil mode fallback menggunakan potongan `knowledge_chunks` dari PBI 1, tetap melewati validasi skema JSON PBI 2, mempertahankan keterangan `"Tidak tercantum dalam potongan pasal yang ditemukan"` untuk pasal tanpa sanksi eksplisit, tetap menolak prediksi vonis, menampilkan banner ramah `"Layanan analisis AI sedang dibatasi. Berikut rekomendasi pasal dari katalog JDIH."`, serta menjaga tombol **Ajukan Pengaduan** tetap aktif dan mengisi otomatis `/pengaduan/buat`.
- **Keamanan Informasi**: Respons publik tidak pernah menyertakan API key maupun angka pemakaian kuota rinci. Administrator dapat memantau kuota harian melalui halaman internal dashboard (`/admin/analitik` atau `/admin/basis-ai`) maupun perintah CLI:
  ```sh
  php artisan bhima:quota-status
  ```
- **Mode Lokal (`mock` / `local_grounded`)**: Saat `LLM_PROVIDER=mock` dan `RAG_LLM_PROVIDER=local_grounded`, sistem berjalan tanpa panggilan jaringan dan tidak dicatat sebagai pemakaian token harian.

## 6. Cara Menguji

1. **Menjalankan Seluruh Pengujian Otomatis (PHPUnit PBI 1 + PBI 2 + PBI 3 + Dashboard)**:
   ```sh
   php artisan test
   ```
   Untuk menjalankan khusus pengujian PBI 2 atau PBI 3:
   ```sh
   php artisan test --filter=LegalAnalysisPbi2Test
   php artisan test --filter=LlmQuotaFallbackPbi3Test
   ```
2. **Menjalankan Skrip Uji 7 Skenario RAG (5 Skenario Kronologi + Luar Cakupan + Anti-Vonis)**:
   ```sh
   php artisan bhima:test-rag
   ```
3. **Menjalankan Server Lokal**:
   ```sh
   php artisan serve
   ```
   - Ruang Konsultasi Warga & Analisis Pasal: `http://localhost:8000/` atau `http://localhost:8000/konsultasi`
   - Formulir Eskalasi Pengaduan Masyarakat: `http://localhost:8000/pengaduan/buat`
   - Detail Sumber Rujukan: `http://localhost:8000/rujukan/JDIH-001`
   - Portal Petugas Internal: `http://localhost:8000/login`
