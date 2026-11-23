<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LlmQuotaService
{
    public const REASON_QUOTA_EXCEEDED = 'quota_exceeded';

    public const REASON_CIRCUIT_OPEN = 'circuit_open';

    public const REASON_MISSING_API_KEY = 'missing_api_key';

    /**
     * Periksa apakah panggilan LLM eksternal diperbolehkan berdasarkan circuit breaker,
     * kuota token harian, dan batas jumlah permintaan harian.
     *
     * @return array{allowed: bool, reason: ?string}
     */
    public function checkAllowance(?int $estimatedTokens = null): array
    {
        if (! $this->tablesReady()) {
            return ['allowed' => true, 'reason' => null];
        }

        $now = $this->now();
        $today = $now->toDateString();

        $row = DB::table('llm_daily_usage')
            ->where('usage_date', $today)
            ->first();

        if ($row && $row->circuit_open_until !== null) {
            $openUntil = Carbon::parse((string) $row->circuit_open_until, $this->timezone());
            if ($openUntil->gt($now)) {
                return ['allowed' => false, 'reason' => self::REASON_CIRCUIT_OPEN];
            }
        }

        $tokensUsed = (int) ($row->tokens_used ?? 0);
        $requestsUsed = (int) ($row->requests_used ?? 0);
        $tokenLimit = max(0, (int) config('llm_quota.daily_token_limit', 200000));
        $requestLimit = max(0, (int) config('llm_quota.daily_request_limit', 500));
        $estimate = max(0, $estimatedTokens ?? (int) config('llm_quota.per_request_token_cap', 3000));

        if ($tokenLimit > 0 && ($tokensUsed + $estimate) > $tokenLimit) {
            return ['allowed' => false, 'reason' => self::REASON_QUOTA_EXCEEDED];
        }

        if ($requestLimit > 0 && $requestsUsed >= $requestLimit) {
            return ['allowed' => false, 'reason' => self::REASON_QUOTA_EXCEEDED];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Catat pemakaian token aktual dari panggilan API yang berhasil secara atomik
     * dan reset hitungan gagal beruntun serta status circuit breaker.
     */
    public function recordUsage(
        string $source,
        string $provider,
        int $promptTokens = 0,
        int $completionTokens = 0,
        ?int $totalTokens = null,
        ?string $sessionUuid = null,
    ): void {
        if (! $this->tablesReady()) {
            return;
        }

        $prompt = max(0, $promptTokens);
        $completion = max(0, $completionTokens);
        $total = $totalTokens !== null ? max(0, $totalTokens) : ($prompt + $completion);
        $now = $this->now();
        $today = $now->toDateString();
        $timestamp = $now->toDateTimeString();

        try {
            DB::transaction(function () use ($today, $timestamp, $source, $provider, $prompt, $completion, $total, $sessionUuid) {
                $this->ensureDailyRow($today, $timestamp);

                $row = DB::table('llm_daily_usage')
                    ->where('usage_date', $today)
                    ->lockForUpdate()
                    ->first();

                DB::table('llm_daily_usage')
                    ->where('usage_date', $today)
                    ->update([
                        'tokens_used' => ((int) ($row->tokens_used ?? 0)) + $total,
                        'requests_used' => ((int) ($row->requests_used ?? 0)) + 1,
                        'consecutive_failures' => 0,
                        'circuit_open_until' => null,
                        'updated_at' => $timestamp,
                    ]);

                DB::table('llm_usage_logs')->insert([
                    'usage_date' => $today,
                    'source' => $this->normalizeSource($source),
                    'provider' => mb_substr($provider, 0, 64),
                    'prompt_tokens' => $prompt,
                    'completion_tokens' => $completion,
                    'total_tokens' => $total,
                    'status' => 'ok',
                    'error_code' => null,
                    'session_uuid' => $sessionUuid ? mb_substr($sessionUuid, 0, 64) : null,
                    'created_at' => $timestamp,
                ]);
            });
        } catch (\Throwable $e) {
            Log::warning('LlmQuotaService: Gagal mencatat pemakaian token LLM.', [
                'source' => $source,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Catat permintaan yang diblokir kuota/circuit/key kosong tanpa menaikkan pemakaian token.
     */
    public function recordBlocked(
        string $source,
        string $provider,
        string $reason,
        ?string $sessionUuid = null,
    ): void {
        if (! $this->tablesReady()) {
            return;
        }

        $now = $this->now();
        $today = $now->toDateString();
        $timestamp = $now->toDateTimeString();

        try {
            DB::transaction(function () use ($today, $timestamp, $source, $provider, $reason, $sessionUuid) {
                $this->ensureDailyRow($today, $timestamp);

                DB::table('llm_usage_logs')->insert([
                    'usage_date' => $today,
                    'source' => $this->normalizeSource($source),
                    'provider' => mb_substr($provider, 0, 64),
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                    'status' => 'quota_blocked',
                    'error_code' => mb_substr($reason, 0, 64),
                    'session_uuid' => $sessionUuid ? mb_substr($sessionUuid, 0, 64) : null,
                    'created_at' => $timestamp,
                ]);
            });
        } catch (\Throwable $e) {
            Log::warning('LlmQuotaService: Gagal mencatat blokir kuota LLM.', [
                'source' => $source,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Catat error panggilan API eksternal secara atomik dan buka circuit breaker
     * apabila gagal beruntun mencapai ambang LLM_CIRCUIT_FAILURE_THRESHOLD.
     *
     * @return bool True bila circuit breaker dalam kondisi terbuka sesudah error ini
     */
    public function recordError(
        string $source,
        string $provider,
        string $errorCode,
        ?string $sessionUuid = null,
        bool $affectsCircuit = true,
        int $promptTokens = 0,
        int $completionTokens = 0,
        int $totalTokens = 0,
    ): bool {
        if (! $this->tablesReady()) {
            return false;
        }

        $prompt = max(0, $promptTokens);
        $completion = max(0, $completionTokens);
        $total = $totalTokens > 0 ? $totalTokens : ($prompt + $completion);
        $now = $this->now();
        $today = $now->toDateString();
        $timestamp = $now->toDateTimeString();
        $threshold = max(1, (int) config('llm_quota.circuit_failure_threshold', 3));
        $cooldownMinutes = max(1, (int) config('llm_quota.circuit_cooldown_minutes', 5));
        $circuitOpened = false;

        try {
            DB::transaction(function () use (
                $today,
                $timestamp,
                $now,
                $source,
                $provider,
                $errorCode,
                $sessionUuid,
                $affectsCircuit,
                $threshold,
                $cooldownMinutes,
                $prompt,
                $completion,
                $total,
                &$circuitOpened
            ) {
                $this->ensureDailyRow($today, $timestamp);

                $row = DB::table('llm_daily_usage')
                    ->where('usage_date', $today)
                    ->lockForUpdate()
                    ->first();

                $updates = ['updated_at' => $timestamp];
                if ($total > 0) {
                    $updates['tokens_used'] = ((int) ($row->tokens_used ?? 0)) + $total;
                    $updates['requests_used'] = ((int) ($row->requests_used ?? 0)) + 1;
                }

                if ($affectsCircuit) {
                    $failures = ((int) ($row->consecutive_failures ?? 0)) + 1;
                    $openUntil = $row->circuit_open_until;

                    if ($failures >= $threshold) {
                        $openUntil = $now->copy()->addMinutes($cooldownMinutes)->toDateTimeString();
                        $circuitOpened = true;
                    } elseif ($openUntil !== null) {
                        $circuitOpened = Carbon::parse((string) $openUntil, $this->timezone())->gt($now);
                    }

                    $updates['consecutive_failures'] = $failures;
                    $updates['circuit_open_until'] = $openUntil;
                }

                if ($affectsCircuit || $total > 0) {
                    DB::table('llm_daily_usage')
                        ->where('usage_date', $today)
                        ->update($updates);
                }

                DB::table('llm_usage_logs')->insert([
                    'usage_date' => $today,
                    'source' => $this->normalizeSource($source),
                    'provider' => mb_substr($provider, 0, 64),
                    'prompt_tokens' => $prompt,
                    'completion_tokens' => $completion,
                    'total_tokens' => $total,
                    'status' => 'error',
                    'error_code' => mb_substr($errorCode, 0, 64),
                    'session_uuid' => $sessionUuid ? mb_substr($sessionUuid, 0, 64) : null,
                    'created_at' => $timestamp,
                ]);
            });
        } catch (\Throwable $e) {
            Log::warning('LlmQuotaService: Gagal mencatat error panggilan LLM.', [
                'source' => $source,
                'error_code' => $errorCode,
                'error' => $e->getMessage(),
            ]);
        }

        return $circuitOpened;
    }

    /**
     * Ambil ringkasan status kuota harian untuk CLI artisan dan dashboard internal admin.
     * JANGAN kirim hasil method ini ke endpoint atau view publik.
     *
     * @return array<string, mixed>
     */
    public function status(?string $date = null): array
    {
        $now = $this->now();
        $usageDate = ($date !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date))
            ? $date
            : $now->toDateString();

        $tokenLimit = max(0, (int) config('llm_quota.daily_token_limit', 200000));
        $requestLimit = max(0, (int) config('llm_quota.daily_request_limit', 500));
        $perRequestCap = max(0, (int) config('llm_quota.per_request_token_cap', 3000));
        $threshold = max(1, (int) config('llm_quota.circuit_failure_threshold', 3));

        $default = [
            'usage_date' => $usageDate,
            'timezone' => $this->timezone(),
            'tokens_used' => 0,
            'daily_token_limit' => $tokenLimit,
            'tokens_remaining' => $tokenLimit,
            'token_usage_percent' => 0.0,
            'requests_used' => 0,
            'daily_request_limit' => $requestLimit,
            'requests_remaining' => $requestLimit,
            'per_request_token_cap' => $perRequestCap,
            'consecutive_failures' => 0,
            'circuit_failure_threshold' => $threshold,
            'circuit_open' => false,
            'circuit_open_until' => null,
            'updated_at' => null,
            'logs_summary' => [
                'ok_count' => 0,
                'quota_blocked_count' => 0,
                'error_count' => 0,
                'fallback_count' => 0,
                'by_source' => [
                    'consultation' => ['requests' => 0, 'tokens' => 0],
                    'analysis' => ['requests' => 0, 'tokens' => 0],
                ],
            ],
        ];

        if (! $this->tablesReady()) {
            return $default;
        }

        $row = DB::table('llm_daily_usage')
            ->where('usage_date', $usageDate)
            ->first();

        $tokensUsed = (int) ($row->tokens_used ?? 0);
        $requestsUsed = (int) ($row->requests_used ?? 0);
        $consecutiveFailures = (int) ($row->consecutive_failures ?? 0);
        $openUntil = $row?->circuit_open_until ? (string) $row->circuit_open_until : null;
        $circuitOpen = $openUntil !== null
            && Carbon::parse($openUntil, $this->timezone())->gt($now);

        $statusCounts = DB::table('llm_usage_logs')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->where('usage_date', $usageDate)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $sourceRows = DB::table('llm_usage_logs')
            ->select(
                'source',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(total_tokens) as sum_tokens')
            )
            ->where('usage_date', $usageDate)
            ->where('status', 'ok')
            ->groupBy('source')
            ->get();

        $bySource = [
            'consultation' => ['requests' => 0, 'tokens' => 0],
            'analysis' => ['requests' => 0, 'tokens' => 0],
        ];
        foreach ($sourceRows as $src) {
            $key = (string) $src->source;
            $bySource[$key] = [
                'requests' => (int) $src->total_requests,
                'tokens' => (int) $src->sum_tokens,
            ];
        }

        $okCount = (int) ($statusCounts['ok'] ?? 0);
        $blockedCount = (int) ($statusCounts['quota_blocked'] ?? 0);
        $errorCount = (int) ($statusCounts['error'] ?? 0);

        return [
            'usage_date' => $usageDate,
            'timezone' => $this->timezone(),
            'tokens_used' => $tokensUsed,
            'daily_token_limit' => $tokenLimit,
            'tokens_remaining' => max(0, $tokenLimit - $tokensUsed),
            'token_usage_percent' => $tokenLimit > 0
                ? round(min(100, ($tokensUsed / $tokenLimit) * 100), 2)
                : 0.0,
            'requests_used' => $requestsUsed,
            'daily_request_limit' => $requestLimit,
            'requests_remaining' => max(0, $requestLimit - $requestsUsed),
            'per_request_token_cap' => $perRequestCap,
            'consecutive_failures' => $consecutiveFailures,
            'circuit_failure_threshold' => $threshold,
            'circuit_open' => $circuitOpen,
            'circuit_open_until' => $openUntil,
            'updated_at' => $row?->updated_at ? (string) $row->updated_at : null,
            'logs_summary' => [
                'ok_count' => $okCount,
                'quota_blocked_count' => $blockedCount,
                'error_count' => $errorCount,
                'fallback_count' => $blockedCount + $errorCount,
                'by_source' => $bySource,
            ],
        ];
    }

    public function today(): string
    {
        return $this->now()->toDateString();
    }

    private function now(): Carbon
    {
        return Carbon::now($this->timezone());
    }

    private function timezone(): string
    {
        $tz = trim((string) config('llm_quota.timezone', config('app.timezone', 'Asia/Jakarta')));

        return $tz !== '' ? $tz : 'Asia/Jakarta';
    }

    private function normalizeSource(string $source): string
    {
        return in_array($source, ['consultation', 'analysis'], true) ? $source : 'analysis';
    }

    private function ensureDailyRow(string $usageDate, string $timestamp): void
    {
        DB::table('llm_daily_usage')->insertOrIgnore([
            'usage_date' => $usageDate,
            'tokens_used' => 0,
            'requests_used' => 0,
            'consecutive_failures' => 0,
            'circuit_open_until' => null,
            'updated_at' => $timestamp,
        ]);
    }

    private function tablesReady(): bool
    {
        try {
            return Schema::hasTable('llm_daily_usage') && Schema::hasTable('llm_usage_logs');
        } catch (\Throwable) {
            return false;
        }
    }
}
