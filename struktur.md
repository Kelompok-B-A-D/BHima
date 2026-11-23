# Struktur Pengerjaan BHIMA: PBI 1, PBI 2, dan PBI 3

Repo: `Kelompok-B-A-D/BHima` (Laravel, MySQL, Vite + Tailwind). Dokumen ini menggantikan versi sebelumnya yang mengasumsikan PHP native.

## 1. Status dan Keterkaitan

| PBI | Isi | Status di repo |
|-----|-----|----------------|
| PBI 1 | Ruang konsultasi dan retrieval dari indeks 100 dokumen JDIH dan SOP | Selesai |
| PBI 2 | Analisis pasal, estimasi sanksi, eskalasi pengaduan (DeepSeek API) | Selesai dengan `LLM_PROVIDER=mock`. Kode DeepSeek siap, belum diuji dengan key asli |
| PBI 3 | Kuota token harian dan fallback ke katalog JDIH | Belum ada |

Ketiganya berhubungan, satu rantai yang sama:

```
Kronologi warga
   |
   v
PBI 1  LegalRetrievalService -> top-K chunk (knowledge_chunks)
   |                                  |
   |                                  +--------------------------+
   v                                                             |
PBI 2  LegalAnalysisService -> DeepSeekProvider (panggilan API)  |
   |        ^                                                    |
   |        | dibungkus kuota + deteksi gangguan                 |
   |   PBI 3  LlmQuotaService                                    |
   |        | kuota habis / API gagal                            |
   |        v                                                    |
   |   PBI 3  Fallback = rekomendasi pasal dari chunk  <---------+
   v
Hasil + tombol "Ajukan Pengaduan" (PBI 2) tetap tampil
```

- **PBI 1 ke PBI 2:** chunk hasil retrieval menjadi konteks analisis. Di kode: `ConsultationController::send()` memanggil `answerChronology()` lalu `analysisService->analyze(..., $result['citations'])`.
- **PBI 1 ke PBI 3:** fallback PBI 3 adalah hasil retrieval PBI 1 (`knowledge_chunks`), bukan sumber data baru. Retrieval PBI 1 juga tidak memanggil API, sehingga aman saat kuota habis.
- **PBI 2 ke PBI 3:** PBI 3 membungkus pemanggil API PBI 2. Hasil fallback wajib tetap lolos skema dan guardrail PBI 2 (tanpa vonis, `Tidak tercantum dalam potongan pasal yang ditemukan` bila sanksi tidak eksplisit, tombol eskalasi aktif).

## 2. Titik Panggil LLM yang Harus Dikuota

Ada **dua** tempat yang memanggil API eksternal, dengan config terpisah. Satu pesan warga bisa memicu keduanya.

| Lokasi | Config | Perilaku gagal saat ini |
|--------|--------|--------------------------|
| `LegalAssistantService::generateFromContext()` | `RAG_LLM_*` (`config/rag.php`) | Turun ke `local_grounded` |
| `Analysis/DeepSeekProvider::analyze()` | `DEEPSEEK_*` (`config/legal_analysis.php`) | Lempar `AnalysisProviderException`, hasil kosong + eskalasi |

Keputusan: kuota memakai **satu penghitung bersama** untuk kedua titik. Samakan key, endpoint, dan model keduanya agar tidak ada dua tagihan.

## 3. Konfigurasi API (endpoint paas.id)

`DeepSeekProvider` otomatis menambah `/chat/completions` bila belum ada.

```dotenv
LLM_PROVIDER=deepseek
DEEPSEEK_API_KEY=            # isi lokal saja, jangan di-commit
DEEPSEEK_BASE_URL=https://ai.paas.id
DEEPSEEK_MODEL=deepseek-v4.1-flash

RAG_LLM_PROVIDER=api
RAG_LLM_BASE_URL=https://ai.paas.id
RAG_LLM_API_KEY=             # sama dengan DEEPSEEK_API_KEY
RAG_LLM_MODEL=deepseek-v4.1-flash
```

Cek sebelum lanjut: `DeepSeekProvider` mengirim `response_format: json_object`. Uji apakah gateway paas.id menerimanya. Bila menolak, hapus field itu dan andalkan `parseJsonContent()` yang sudah ada.

## 4. Pekerjaan PBI 3

### 4.1 File baru dan file yang diubah

```
config/llm_quota.php                              BARU  batas dan pengaturan circuit breaker
database/migrations/2026_11_xx_add_llm_quota.php  BARU  tabel llm_usage_logs, llm_daily_usage
app/Services/LlmQuotaService.php                  BARU  cek, catat, circuit breaker
app/Services/Analysis/QuotaAwareProvider.php      BARU  decorator AnalysisProvider
app/Services/CatalogFallbackService.php           BARU  rekomendasi pasal dari knowledge_chunks
app/Console/Commands/QuotaStatus.php              BARU  php artisan bhima:quota-status
tests/Feature/LlmQuotaFallbackPbi3Test.php        BARU

app/Providers/AppServiceProvider.php              UBAH  bind AnalysisProvider ke QuotaAwareProvider
app/Services/Analysis/DeepSeekProvider.php        UBAH  kirim usage.total_tokens ke LlmQuotaService
app/Services/LegalAssistantService.php            UBAH  cek kuota sebelum Http::post, catat usage
app/Services/LegalAnalysisService.php             UBAH  catch error -> fallback, bukan hasil kosong
app/Http/Controllers/ConsultationController.php   UBAH  teruskan meta mode/fallback ke respons
resources/views/consultation/index.blade.php      UBAH  banner mode fallback
resources/js/consultation/app.js                  UBAH  render banner dan label sumber
.env.example                                      UBAH  variabel kuota, hapus kredensial DB asli
README.md                                         UBAH  bagian PBI 3
```

### 4.2 Skema database

```
llm_daily_usage
  id, usage_date (unique, zona waktu Asia/Jakarta), tokens_used, requests_used,
  circuit_open_until (nullable), updated_at

llm_usage_logs
  id, usage_date, source (consultation | analysis), provider,
  prompt_tokens, completion_tokens, total_tokens,
  status (ok | quota_blocked | error), error_code (nullable),
  session_uuid (nullable), created_at
```

Penambahan penghitung harus atomik (`DB::table(...)->increment(...)` atau `lockForUpdate()` dalam transaksi) agar tidak salah hitung saat permintaan bersamaan.

### 4.3 Konfigurasi `config/llm_quota.php`

```dotenv
LLM_DAILY_TOKEN_LIMIT=200000
LLM_DAILY_REQUEST_LIMIT=500
LLM_PER_REQUEST_TOKEN_CAP=3000     # estimasi maksimum satu panggilan
LLM_CIRCUIT_FAILURE_THRESHOLD=3    # gagal beruntun sebelum API dihentikan sementara
LLM_CIRCUIT_COOLDOWN_MINUTES=5
```

### 4.4 Alur keputusan

```
sebelum panggil API:
  1. circuit_open_until masih aktif?                     -> fallback (alasan: circuit_open)
  2. tokens_used + estimasi > LLM_DAILY_TOKEN_LIMIT?     -> fallback (alasan: quota_exceeded)
  3. requests_used >= LLM_DAILY_REQUEST_LIMIT?           -> fallback (alasan: quota_exceeded)
  4. lainnya                                             -> panggil API

sesudah panggil API:
  - sukses  : catat usage.total_tokens, reset hitungan gagal beruntun
  - 429 / 5xx / timeout / invalid_json : catat error, tambah hitungan gagal,
        bila >= threshold buka circuit, lalu fallback (alasan: rate_limit | http_error | timeout)
  - missing_api_key : fallback (alasan: missing_api_key)
```

### 4.5 Isi fallback

`CatalogFallbackService` memakai chunk dari `LegalRetrievalService` (PBI 1) dan menyusun keluaran dengan skema PBI 2 yang sama (`rujukan`, `pasal_relevan`, `ancaman_sanksi`, `perlu_eskalasi`, `catatan`). `MockProvider` sudah melakukan hal ini dari chunk, jadi **gunakan ulang logikanya**, jangan tulis ulang.

Tambahkan ke `meta`:

```json
{
  "mode": "fallback",
  "fallback_reason": "quota_exceeded",
  "provider": "catalog_fallback"
}
```

Saat normal: `"mode": "ai"`. Jangan kirim angka kuota rinci ke publik. Cukup `mode` dan alasan umum.

### 4.6 Antarmuka warga

- Banner di ruang konsultasi saat `mode = fallback`: "Layanan analisis AI sedang dibatasi. Berikut rekomendasi pasal dari katalog JDIH."
- Hasil retrieval dan sitasi tetap tampil seperti biasa.
- Tombol **Ajukan Pengaduan** tetap muncul dan tetap mengisi otomatis `/pengaduan/buat`.
- Pesan error teknis (HTTP, timeout) tidak ditampilkan mentah ke warga.

### 4.7 Dashboard internal (opsional, setelah inti selesai)

Tampilkan pemakaian token hari ini dan jumlah fallback pada halaman `analitik` atau `pengaturan` melalui `DashboardDataController`.

## 5. Urutan Kerja

| No | Tugas | Hasil |
|----|-------|-------|
| 1 | Isi `.env` lokal, ubah `.env.example`, `php artisan config:clear` | Config siap |
| 2 | Uji koneksi ke paas.id lewat `DeepSeekProvider` dengan key asli | Respons 200 dan JSON valid (cek `response_format`) |
| 3 | Migrasi `llm_daily_usage` dan `llm_usage_logs` | Tabel ada |
| 4 | `LlmQuotaService` (cek, catat, circuit breaker) | Unit teruji |
| 5 | Pasang pencatatan usage di `DeepSeekProvider` dan `LegalAssistantService` | Penghitung terisi |
| 6 | `CatalogFallbackService` dengan logika `MockProvider` | Fallback sesuai skema PBI 2 |
| 7 | `QuotaAwareProvider` dan bind di `AppServiceProvider` | Alur otomatis |
| 8 | Ubah `LegalAnalysisService` agar error mengarah ke fallback | Tidak ada hasil kosong karena API |
| 9 | Banner UI dan meta di respons | Warga melihat mode fallback |
| 10 | `LlmQuotaFallbackPbi3Test`, `php artisan test`, update README | Semua hijau |

## 6. Pengujian PBI 3

Mengikuti pola `LegalAnalysisPbi2Test` (pakai `Http::fake()`).

1. Kuota habis: `Http::assertNothingSent()`, respons `mode=fallback`, `fallback_reason=quota_exceeded`, pasal tetap muncul.
2. API 429, 500, dan timeout: fallback muncul, aplikasi tidak error.
3. Gagal beruntun mencapai batas: circuit terbuka, panggilan berikutnya tidak menyentuh jaringan, tertutup lagi setelah cooldown.
4. Penghitung bertambah sesuai `usage.total_tokens`; hari baru (`Carbon::setTestNow`) mereset hitungan.
5. Fallback lolos `LegalAnalysisService::isValidSchema()` dan guardrail vonis.
6. Tombol eskalasi tetap aktif dan formulir pengaduan terisi pada mode fallback.
7. Respons publik dan view tidak pernah memuat API key atau angka kuota rinci.
8. `LLM_PROVIDER=mock` tetap berjalan tanpa tercatat sebagai pemakaian token.

## 7. Risiko dan Catatan

- **Kredensial di repo:** `.env.example` memuat host dan password database yang tampak asli. Ganti dengan placeholder dan ganti password database.
- **API key:** key yang ditempel di chat sebaiknya diganti setelah pengembangan. Simpan hanya di `.env` server.
- **Dua penghitung:** jangan biarkan `RAG_LLM_*` dan `DEEPSEEK_*` dihitung terpisah, kuota harian jadi tidak akurat.
- **Estimasi sebelum panggil:** angka pasti baru ada setelah respons. Pakai estimasi `LLM_PER_REQUEST_TOKEN_CAP` untuk pengecekan awal, lalu koreksi dengan `usage` asli.
- **Zona waktu:** pastikan `APP_TIMEZONE=Asia/Jakarta` agar reset harian jatuh pada tengah malam WIB.

## 8. Kriteria Selesai

- [ ] Satu penghitung token harian mencakup konsultasi dan analisis
- [ ] Kuota habis atau API gagal otomatis menghasilkan rekomendasi pasal dari katalog JDIH
- [ ] Tidak ada panggilan jaringan saat kuota habis atau circuit terbuka
- [ ] Hasil fallback sesuai skema PBI 2 dan guardrail tetap berlaku
- [ ] Banner mode fallback tampil, tombol Ajukan Pengaduan tetap aktif
- [ ] Penghitung reset setiap hari
- [ ] `php artisan test` lulus termasuk `LlmQuotaFallbackPbi3Test`
- [ ] API key dan kredensial DB tidak ada di repositori
