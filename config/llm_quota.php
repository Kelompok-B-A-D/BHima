<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kuota Token Harian & Circuit Breaker LLM (PBI 3)
    |--------------------------------------------------------------------------
    |
    | Satu penghitung bersama untuk seluruh pemanggilan LLM eksternal
    | (konsultasi RAG maupun analisis pasal DeepSeek). Reset harian mengikuti
    | zona waktu APP_TIMEZONE (Asia/Jakarta).
    |
    */

    'daily_token_limit' => (int) env('LLM_DAILY_TOKEN_LIMIT', 200000),

    'daily_request_limit' => (int) env('LLM_DAILY_REQUEST_LIMIT', 500),

    'per_request_token_cap' => (int) env('LLM_PER_REQUEST_TOKEN_CAP', 3000),

    'circuit_failure_threshold' => (int) env('LLM_CIRCUIT_FAILURE_THRESHOLD', 3),

    'circuit_cooldown_minutes' => (int) env('LLM_CIRCUIT_COOLDOWN_MINUTES', 5),

    'timezone' => (string) env('APP_TIMEZONE', 'Asia/Jakarta'),

    'fallback_banner_message' => 'Layanan analisis AI sedang dibatasi. Berikut rekomendasi pasal dari katalog JDIH.',
];
