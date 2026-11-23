<?php

namespace App\Console\Commands;

use App\Services\LlmQuotaService;
use Illuminate\Console\Command;

class QuotaStatus extends Command
{
    protected $signature = 'bhima:quota-status
        {--date= : Tanggal kuota dalam format YYYY-MM-DD (default hari ini zona waktu Asia/Jakarta)}
        {--json : Tampilkan status kuota dalam format JSON}';

    protected $description = 'Menampilkan status pemakaian kuota token harian LLM, jumlah fallback, dan circuit breaker (PBI 3)';

    public function handle(LlmQuotaService $quotaService): int
    {
        $dateOption = $this->option('date') ? trim((string) $this->option('date')) : null;
        if ($dateOption !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOption)) {
            $this->error('Format tanggal tidak valid. Gunakan format YYYY-MM-DD (contoh: 2026-11-24).');

            return self::FAILURE;
        }

        $status = $quotaService->status($dateOption);

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Status Kuota LLM BHIMA (%s · %s)',
            $status['usage_date'],
            $status['timezone']
        ));

        $circuitText = $status['circuit_open']
            ? 'TERBUKA sampai '.$status['circuit_open_until']
            : 'Normal (tertutup)';

        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Token Terpakai / Batas Harian', sprintf('%s / %s (%.2f%%)', number_format($status['tokens_used']), number_format($status['daily_token_limit']), $status['token_usage_percent'])],
                ['Sisa Token Harian', number_format($status['tokens_remaining'])],
                ['Permintaan API / Batas Harian', sprintf('%s / %s', number_format($status['requests_used']), number_format($status['daily_request_limit']))],
                ['Estimasi Kapasitas per Panggilan', number_format($status['per_request_token_cap']).' token'],
                ['Status Circuit Breaker', $circuitText],
                ['Gagal Beruntun / Ambang', sprintf('%d / %d', $status['consecutive_failures'], $status['circuit_failure_threshold'])],
                ['Panggilan Sukses (ok)', number_format($status['logs_summary']['ok_count'])],
                ['Diblokir Kuota / Circuit', number_format($status['logs_summary']['quota_blocked_count'])],
                ['Error API Eksternal', number_format($status['logs_summary']['error_count'])],
                ['Total Mode Fallback', number_format($status['logs_summary']['fallback_count'])],
            ]
        );

        $bySource = $status['logs_summary']['by_source'];
        $this->table(
            ['Sumber Panggilan', 'Permintaan Sukses', 'Total Token'],
            [
                ['consultation (RAG)', number_format($bySource['consultation']['requests'] ?? 0), number_format($bySource['consultation']['tokens'] ?? 0)],
                ['analysis (DeepSeek)', number_format($bySource['analysis']['requests'] ?? 0), number_format($bySource['analysis']['tokens'] ?? 0)],
            ]
        );

        return self::SUCCESS;
    }
}
