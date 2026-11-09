<?php

namespace App\Services\Analysis;

use App\Contracts\AnalysisProvider;
use App\Exceptions\AnalysisProviderException;
use App\Services\LegalAnalysisService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekProvider implements AnalysisProvider
{
    public function name(): string
    {
        return 'deepseek';
    }

    /**
     * Panggil endpoint Chat Completions DeepSeek API untuk menghasilkan analisis pasal terstruktur.
     * Tidak dieksekusi ke jaringan selama DEEPSEEK_API_KEY kosong.
     *
     * @param  list<array<string, mixed>>  $chunks
     */
    public function analyze(string $chronology, array $chunks): array
    {
        if (empty($chunks)) {
            return [
                'rujukan' => [],
                'pasal_relevan' => [],
                'ancaman_sanksi' => [],
                'perlu_eskalasi' => true,
                'catatan' => 'Konteks pasal JDIH atau SOP layanan tidak ditemukan untuk kronologi ini. Silakan ajukan pengaduan agar ditindaklanjuti oleh petugas.',
            ];
        }

        $apiKey = trim((string) config('legal_analysis.deepseek.api_key', ''));
        if ($apiKey === '') {
            Log::error('DeepSeekProvider: DEEPSEEK_API_KEY kosong saat LLM_PROVIDER=deepseek dipanggil.');
            throw new AnalysisProviderException(
                'Layanan analisis DeepSeek belum diaktifkan karena kunci API belum tersedia pada konfigurasi server. Silakan hubungi administrator atau gunakan penyedia bawaan.',
                'missing_api_key'
            );
        }

        $baseUrl = rtrim((string) config('legal_analysis.deepseek.base_url', 'https://api.deepseek.com/v1'), '/');
        $endpoint = str_ends_with($baseUrl, '/chat/completions')
            ? $baseUrl
            : $baseUrl.'/chat/completions';
        $timeout = max(3, (int) config('legal_analysis.deepseek.timeout', 20));
        $maxRetries = max(0, min(1, (int) config('legal_analysis.deepseek.max_retries', 1)));

        $payload = $this->buildRequestPayload($chronology, $chunks);

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withToken($apiKey)
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($endpoint, $payload);
            } catch (ConnectionException $e) {
                Log::error('DeepSeekProvider: Permintaan ke DeepSeek API mengalami timeout atau kegagalan jaringan.', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);

                throw new AnalysisProviderException(
                    'Waktu tunggu koneksi ke layanan analisis hukum habis (timeout). Silakan coba beberapa saat lagi.',
                    'timeout',
                    0,
                    $e
                );
            } catch (\Throwable $e) {
                Log::error('DeepSeekProvider: Kesalahan tak terduga saat menghubungi DeepSeek API.', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);

                throw new AnalysisProviderException(
                    'Maaf, layanan analisis hukum sedang mengalami kendala komunikasi. Silakan coba kembali.',
                    'network_error',
                    0,
                    $e
                );
            }

            if ($response->status() === 429) {
                Log::warning('DeepSeekProvider: Rate limit DeepSeek API tercapai (HTTP 429).', [
                    'status' => 429,
                    'attempt' => $attempt + 1,
                ]);

                throw new AnalysisProviderException(
                    'Layanan analisis hukum sedang sibuk karena batas permintaan tercapai (rate limit). Silakan tunggu sejenak lalu coba lagi.',
                    'rate_limit'
                );
            }

            if (! $response->successful()) {
                Log::error('DeepSeekProvider: DeepSeek API mengembalikan status HTTP error.', [
                    'status' => $response->status(),
                    'attempt' => $attempt + 1,
                ]);

                throw new AnalysisProviderException(
                    'Maaf, layanan analisis hukum sedang mengalami gangguan teknis sementara (HTTP '.$response->status().'). Silakan coba beberapa saat lagi.',
                    'http_error'
                );
            }

            $rawContent = trim((string) $response->json('choices.0.message.content', ''));
            $decoded = $this->parseJsonContent($rawContent);

            if (is_array($decoded) && LegalAnalysisService::isValidSchema($decoded)) {
                return $decoded;
            }

            Log::warning('DeepSeekProvider: Respons JSON dari DeepSeek tidak sesuai skema tetap.', [
                'attempt' => $attempt + 1,
                'max_retries' => $maxRetries,
            ]);
        }

        Log::error('DeepSeekProvider: Gagal mendapatkan respons JSON sesuai skema setelah batas retry maksimal.');

        throw new AnalysisProviderException(
            'Maaf, format hasil analisis hukum belum dapat diverifikasi saat ini. Silakan coba lagi atau langsung gunakan tombol Ajukan Pengaduan.',
            'invalid_json'
        );
    }

    /**
     * Susun payload permintaan ke DeepSeek API.
     * Hanya mengirim kronologi yang telah di-masking dari data identitas pribadi dan chunk yang diperlukan.
     *
     * @param  list<array<string, mixed>>  $chunks
     * @return array<string, mixed>
     */
    public function buildRequestPayload(string $chronology, array $chunks): array
    {
        $maskedChronology = self::maskPersonalIdentifiers($chronology);
        $systemPrompt = (string) config('legal_analysis.system_prompt');
        $model = (string) config('legal_analysis.deepseek.model', 'deepseek-chat');
        $temperature = min(0.2, max(0.0, (float) config('legal_analysis.deepseek.temperature', 0.1)));
        $maxTokens = max(256, (int) config('legal_analysis.deepseek.max_tokens', 1500));

        $contextBlocks = [];
        foreach ($chunks as $idx => $chunk) {
            $num = $idx + 1;
            $docNumber = trim((string) ($chunk['document_number'] ?? ''));
            $docYear = trim((string) ($chunk['document_year'] ?? ''));
            $nomorTahun = ($docYear !== '' && ! str_contains(mb_strtolower($docNumber, 'UTF-8'), mb_strtolower($docYear, 'UTF-8')))
                ? trim($docNumber.' Tahun '.$docYear)
                : $docNumber;

            $contextBlocks[] = sprintf(
                "[CHUNK #%d]\nDokumen: %s\nNomor/Tahun: %s\nPasal: %s\nKategori: %s\nTautan: %s\nTeks Pasal: %s",
                $num,
                (string) ($chunk['document_name'] ?? ''),
                $nomorTahun,
                (string) ($chunk['article_label'] ?? ''),
                (string) ($chunk['category'] ?? 'JDIH'),
                (string) ($chunk['source_url'] ?? ''),
                self::maskPersonalIdentifiers((string) ($chunk['content'] ?? ''))
            );
        }

        $userPrompt = "KRONOLOGI WARGA (DATA IDENTITAS TELAH DISAMARKAN):\n"
            .$maskedChronology
            ."\n\nKONTEKS PASAL JDIH & SOP HASIL RETRIEVAL:\n"
            .implode("\n\n", $contextBlocks);

        return [
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];
    }

    /**
     * Samarkan data identitas warga (NIK/KK, nomor telepon, email, nama, dan alamat eksplisit)
     * sebelum dikirim ke provider eksternal.
     */
    public static function maskPersonalIdentifiers(string $text): string
    {
        // 1. Samarkan 16 digit NIK / Nomor KK
        $masked = preg_replace('/\b\d{16}\b/', '[NIK/KK-DISAMARKAN]', $text) ?? $text;

        // 2. Samarkan nomor telepon Indonesia (+628..., 628..., 08..., atau 021...)
        $masked = preg_replace('/(?:\+62|62|0)8\d{8,11}\b/', '[TELEPON-DISAMARKAN]', $masked) ?? $masked;
        $masked = preg_replace('/\b0\d{2,3}[-\s]?\d{6,8}\b/', '[TELEPON-DISAMARKAN]', $masked) ?? $masked;

        // 3. Samarkan alamat email
        $masked = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[EMAIL-DISAMARKAN]', $masked) ?? $masked;

        // 4. Samarkan pola nama eksplisit (contoh: "Nama saya Budi Santoso", "Nama: Siti Rahma")
        $masked = preg_replace(
            '/\b((?i:nama\s+saya|nama\s+lengkap|atas\s+nama|nama\s+pelapor|nama))\s*(?:(?i:adalah|ialah)|[:=-])\s*([A-Z][a-zA-Z\'.]+(?:\s+[A-Z][a-zA-Z\'.]+){0,3})/u',
            '$1: [NAMA-DISAMARKAN]',
            $masked
        ) ?? $masked;
        $masked = preg_replace(
            '/\b((?i:saya\s+bernama|nama\s+saya))\s+([A-Z][a-zA-Z\'.]+(?:\s+[A-Z][a-zA-Z\'.]+){0,2})/u',
            '$1 [NAMA-DISAMARKAN]',
            $masked
        ) ?? $masked;

        // 5. Samarkan pola alamat eksplisit (contoh: "Alamat: Jl. Merdeka No. 10...", "bertempat tinggal di Jl. ...")
        $masked = preg_replace(
            '/\b(alamat\s+saya|alamat\s+lengkap|alamat\s+rumah|bertempat\s+tinggal\s+di|berdomisili\s+di|alamat)\s*[:=-]?\s*((?:Jl\.|Jalan|Gg\.|Gang|RT|RW|Desa|Kelurahan|Kecamatan)(?:[^\n,;.]|\.(?=\s*(?:[0-9]|No|RT|RW|Kel|Kec|Blok|Kav)))+)/iu',
            '$1 [ALAMAT-DISAMARKAN]',
            $masked
        ) ?? $masked;

        return $masked;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonContent(string $rawContent): ?array
    {
        if ($rawContent === '') {
            return null;
        }

        $clean = trim($rawContent);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
            $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;
            $clean = trim($clean);
        }

        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : null;
    }
}
