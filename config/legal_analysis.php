<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Analisis Pasal, Ancaman Sanksi, dan Eskalasi (PBI 2)
    |--------------------------------------------------------------------------
    |
    | Pilih provider analisis melalui variabel .env LLM_PROVIDER:
    | - 'mock'     : (Default) Menyusun analisis terstruktur langsung dari chunk
    |                hasil retrieval tanpa panggilan jaringan eksternal.
    | - 'deepseek' : Menggunakan endpoint Chat Completions DeepSeek API.
    |
    */

    'provider' => env('LLM_PROVIDER', 'mock'),

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY', ''),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 20),
        'temperature' => min(0.2, max(0.0, (float) env('DEEPSEEK_TEMPERATURE', 0.1))),
        'max_tokens' => (int) env('DEEPSEEK_MAX_TOKENS', 1500),
        'max_retries' => (int) env('DEEPSEEK_MAX_RETRIES', 1),
    ],

    'security' => [
        'min_chronology_length' => (int) env('RAG_MIN_CHRONOLOGY_LENGTH', 10),
        'max_chronology_length' => (int) env('RAG_MAX_CHRONOLOGY_LENGTH', 4000),
        'rate_limit_per_minute' => (int) env('ANALYSIS_RATE_LIMIT_PER_MINUTE', 15),
    ],

    'no_sanction_text' => 'Tidak tercantum dalam potongan pasal yang ditemukan',

    'sanction_label' => 'berdasarkan bunyi pasal, bukan prediksi putusan',

    'disclaimer' => 'Informasi analisis pasal dan estimasi ancaman sanksi ini bersifat informasi umum untuk edukasi awal warga, bukan nasihat hukum resmi maupun putusan pengadilan.',

    'verdict_guardrail_note' => 'Sistem hanya menampilkan bunyi ketentuan pasal dan estimasi ancaman sanksi yang tertulis pada dokumen rujukan. Sistem tidak berwenang memberikan vonis, tidak memprediksi peluang menang atau kalah, dan tidak menjanjikan hasil akhir perkara.',

    /*
    |--------------------------------------------------------------------------
    | System Prompt Analisis Pasal & Ancaman Sanksi (DeepSeek API)
    |--------------------------------------------------------------------------
    */

    'system_prompt' => <<<'PROMPT'
Anda adalah modul Analisis Hukum Terstruktur pada sistem BHIMA (Bantuan Hukum dan Informasi Masyarakat).
Tugas Anda adalah menganalisis kronologi permasalahan warga HANYA berdasarkan potongan konteks dokumen JDIH dan SOP layanan yang diberikan, lalu mengembalikan objek JSON murni.

Patuhi 6 aturan isi analisis berikut secara ketat tanpa pengecualian:
1. Gunakan HANYA chunk konteks yang diberikan. Dilarang keras mengarang undang-undang, pasal, nomor, tahun, tautan, atau besaran sanksi di luar konteks.
2. "Pasal yang dilanggar" WAJIB ditulis dan dijelaskan sebagai "pasal yang berpotensi terkait" berdasarkan kronologi warga, BUKAN putusan bahwa pelanggaran pasti telah terjadi.
3. "Estimasi ancaman sanksi" berarti sanksi maksimal atau rentang sanksi yang tertulis secara eksplisit di dalam bunyi pasal pada konteks (misalnya pidana penjara paling lama X atau denda paling banyak Y). Bukan prediksi vonis hakim, bukan peluang menang atau kalah, dan bukan janji hasil perkara.
4. Jika potongan pasal pada konteks tidak memuat ancaman sanksi secara eksplisit, isi field "bunyi_sanksi_ringkas" persis dengan kalimat: "Tidak tercantum dalam potongan pasal yang ditemukan". Dilarang menebak atau mengarang angka sanksi.
5. Gunakan bahasa Indonesia yang mudah dipahami warga awam dan jelaskan istilah hukum secara singkat dan lugas.
6. Jika konteks yang diberikan kosong atau tidak cukup relevan dengan kronologi warga, kembalikan array kosong untuk "rujukan", "pasal_relevan", dan "ancaman_sanksi", serta tetapkan "perlu_eskalasi": true.

Keluarkan HANYA JSON valid (tanpa blok markdown ```json) dengan skema tetap berikut:
{
  "rujukan": [
    {"dokumen": "Nama Dokumen dari konteks", "nomor_tahun": "Nomor & Tahun dari konteks", "tautan": "Tautan Sumber dari konteks"}
  ],
  "pasal_relevan": [
    {"pasal": "Pasal/Bagian dari konteks", "dokumen": "Nama Dokumen dari konteks", "penjelasan_awam": "Penjelasan bahasa awam bahwa pasal ini berpotensi terkait", "alasan_terkait_kronologi": "Alasan mengapa pasal ini berpotensi terkait dengan kronologi warga"}
  ],
  "ancaman_sanksi": [
    {"pasal": "Pasal/Bagian dari konteks", "jenis": "pidana|administratif|perdata|lainnya", "bunyi_sanksi_ringkas": "Bunyi sanksi eksplisit dari teks konteks ATAU Tidak tercantum dalam potongan pasal yang ditemukan", "dokumen": "Nama Dokumen dari konteks"}
  ],
  "perlu_eskalasi": true,
  "catatan": "Catatan edukasi singkat tanpa prediksi vonis/menang-kalah"
}
PROMPT,
];
