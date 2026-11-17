<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Retrieval-Augmented Generation (RAG) BHIMA
    |--------------------------------------------------------------------------
    */

    'retrieval' => [
        'top_k' => (int) env('RAG_TOP_K', 5),
        'min_similarity' => (float) env('RAG_MIN_SIMILARITY', 0.26),
        'semantic_weight' => (float) env('RAG_SEMANTIC_WEIGHT', 0.60),
        'keyword_weight' => (float) env('RAG_KEYWORD_WEIGHT', 0.40),
        'chunk_max_chars' => (int) env('RAG_CHUNK_MAX_CHARS', 600),
        'chunk_overlap_chars' => (int) env('RAG_CHUNK_OVERLAP_CHARS', 120),
    ],

    'embedding' => [
        'provider' => env('RAG_EMBEDDING_PROVIDER', 'local'), // local | api
        'base_url' => env('RAG_EMBEDDING_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('RAG_EMBEDDING_API_KEY', env('RAG_API_KEY')),
        'model' => env('RAG_EMBEDDING_MODEL', 'bhima-legal-embed-v1'),
        'dimensions' => (int) env('RAG_EMBEDDING_DIMENSIONS', 128),
        'timeout' => (int) env('RAG_EMBEDDING_TIMEOUT', 10),
    ],

    'llm' => [
        'provider' => env('RAG_LLM_PROVIDER', 'local_grounded'), // local_grounded | api
        'base_url' => env('RAG_LLM_BASE_URL', 'https://api.deepseek.com/v1'),
        'api_key' => env('RAG_LLM_API_KEY', env('RAG_API_KEY')),
        'model' => env('RAG_LLM_MODEL', 'deepseek-chat'),
        'temperature' => (float) env('RAG_LLM_TEMPERATURE', 0.1),
        'max_tokens' => (int) env('RAG_LLM_MAX_TOKENS', 1200),
        'timeout' => (int) env('RAG_LLM_TIMEOUT', 20),
    ],

    'security' => [
        'min_chronology_length' => (int) env('RAG_MIN_CHRONOLOGY_LENGTH', 10),
        'max_chronology_length' => (int) env('RAG_MAX_CHRONOLOGY_LENGTH', 4000),
        'rate_limit_per_minute' => (int) env('RAG_RATE_LIMIT_PER_MINUTE', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompt Asisten Hukum Virtual BHIMA
    |--------------------------------------------------------------------------
    */

    'system_prompt' => <<<'PROMPT'
Anda adalah Asisten Hukum Virtual BHIMA (Bantuan Hukum dan Informasi Masyarakat). Tugas Anda adalah membantu warga memahami aturan hukum dan prosedur layanan publik berdasarkan potongan dokumen JDIH dan SOP layanan yang diberikan.

Patuhi 7 aturan wajib berikut tanpa pengecualian:
1. Jawab dalam bahasa Indonesia yang sederhana dan mudah dipahami warga awam. Hindari istilah hukum yang rumit tanpa penjelasan singkat.
2. Jawab HANYA berdasarkan potongan konteks yang diberikan. Dilarang keras mengarang pasal, nomor, tahun, atau isi aturan di luar konteks.
3. Susun jawaban dengan format 4 bagian berikut:
   (a) Ringkasan Masalah: ringkas permasalahan warga dalam 1-2 kalimat.
   (b) Pasal/Aturan yang Relevan dan Artinya: jelaskan aturan yang berlaku dan maknanya dalam bahasa awam.
   (c) Langkah yang Bisa Dilakukan Warga: berikan langkah tindak lanjut praktis sesuai aturan.
   (d) Rujukan SOP Layanan: cantumkan prosedur layanan terkait dari SOP bila tersedia pada konteks.
4. Setiap klaim hukum WAJIB disertai sitasi lengkap yang mencakup: nama dokumen, nomor/tahun, pasal/bagian, dan tautan sumber dari konteks yang diberikan.
5. DILARANG memberi vonis, memprediksi hasil perkara (seperti menentukan menang/kalah atau berat hukuman), atau menjanjikan hasil tertentu.
6. Jika konteks yang tersedia tidak cukup untuk menjawab seluruh pertanyaan warga, katakan secara terus terang dan sarankan warga menghubungi petugas layanan BHIMA atau lembaga bantuan hukum terakreditasi.
7. Akhiri setiap jawaban dengan satu kalimat pengingat singkat bahwa jawaban bersifat informasi awal dan bukan nasihat hukum resmi.
PROMPT,

    'disclaimer' => 'Penjelasan di atas bersifat informasi hukum awal berdasarkan dokumen yang terindeks dan bukan merupakan nasihat hukum resmi maupun putusan hukum.',

    'out_of_scope_message' => 'Maaf, kami belum menemukan pasal atau SOP layanan yang cukup relevan dengan kronologi yang Anda sampaikan pada basis pengetahuan BHIMA saat ini. Agar permasalahan Anda ditangani dengan tepat dan tidak terjadi kesalahan informasi hukum, silakan hubungi petugas verifikator kami, sampaikan laporan melalui kanal Pengaduan Masyarakat BHIMA, atau kunjungi Pos Bantuan Hukum (Posbakum) terdekat.',

    'verdict_guardrail_notice' => 'Penting: Asisten Hukum Virtual tidak berwenang memberikan vonis, tidak dapat memastikan menang atau kalah dalam suatu perkara, dan tidak menjanjikan hasil akhir karena penilaian pembuktian serta putusan merupakan kewenangan pejabat atau majelis hakim yang berwenang.',
];

