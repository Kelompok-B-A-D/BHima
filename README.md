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

# Provider LLM Generasi Jawaban: 'local_grounded' (default) atau 'api' (DeepSeek/OpenAI-compatible)
RAG_LLM_PROVIDER=local_grounded
RAG_LLM_BASE_URL=https://api.deepseek.com/v1
RAG_LLM_API_KEY=
RAG_LLM_MODEL=deepseek-chat
RAG_LLM_TEMPERATURE=0.1
RAG_LLM_MAX_TOKENS=1200
RAG_LLM_TIMEOUT=20

# Keamanan & Pembatasan Konsultasi
RAG_MIN_CHRONOLOGY_LENGTH=10
RAG_MAX_CHRONOLOGY_LENGTH=4000
RAG_RATE_LIMIT_PER_MINUTE=15
```

System prompt asisten disimpan secara terpisah di [`config/rag.php`](config/rag.php).

## 4. Cara Menguji

1. **Menjalankan Seluruh Pengujian Otomatis (PHPUnit)**:
   ```sh
   php artisan test
   ```
2. **Menjalankan Skrip Uji 7 Skenario RAG (5 Skenario Kronologi + Luar Cakupan + Anti-Vonis)**:
   ```sh
   php artisan bhima:test-rag
   ```
3. **Menjalankan Server Lokal**:
   ```sh
   php artisan serve
   ```
   - Ruang Konsultasi Warga: `http://localhost:8000/` atau `http://localhost:8000/konsultasi`
   - Detail Sumber Rujukan: `http://localhost:8000/rujukan/JDIH-001`
   - Portal Petugas Internal: `http://localhost:8000/login`
