<?php

namespace App\Services\Analysis;

use App\Contracts\AnalysisProvider;
use App\Exceptions\AnalysisProviderException;
use App\Services\LlmQuotaService;

class QuotaAwareProvider implements AnalysisProvider
{
    private ?string $sessionUuid = null;

    public function __construct(
        private readonly AnalysisProvider $inner,
        private readonly LlmQuotaService $quotaService,
    ) {}

    public function name(): string
    {
        return $this->inner->name();
    }

    public function inner(): AnalysisProvider
    {
        return $this->inner;
    }

    public function setSessionUuid(?string $sessionUuid): void
    {
        $this->sessionUuid = $sessionUuid;

        if ($this->inner instanceof DeepSeekProvider) {
            $this->inner->setSessionUuid($sessionUuid);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $chunks
     */
    public function analyze(string $chronology, array $chunks): array
    {
        if (empty($chunks) || $this->inner instanceof MockProvider) {
            return $this->inner->analyze($chronology, $chunks);
        }

        $sessionUuid = $this->resolveSessionUuid();
        if ($this->inner instanceof DeepSeekProvider) {
            $this->inner->setSessionUuid($sessionUuid);
        }

        $allowance = $this->quotaService->checkAllowance();
        if (! $allowance['allowed']) {
            $reason = (string) ($allowance['reason'] ?? LlmQuotaService::REASON_QUOTA_EXCEEDED);
            $this->quotaService->recordBlocked('analysis', $this->inner->name(), $reason, $sessionUuid);

            throw new AnalysisProviderException(
                (string) config(
                    'llm_quota.fallback_banner_message',
                    'Layanan analisis AI sedang dibatasi. Berikut rekomendasi pasal dari katalog JDIH.'
                ),
                $reason
            );
        }

        try {
            return $this->inner->analyze($chronology, $chunks);
        } catch (AnalysisProviderException $e) {
            if ($e->errorCode === LlmQuotaService::REASON_MISSING_API_KEY) {
                $this->quotaService->recordBlocked('analysis', $this->inner->name(), $e->errorCode, $sessionUuid);
            } else {
                $this->quotaService->recordError(
                    'analysis',
                    $this->inner->name(),
                    $e->errorCode,
                    $sessionUuid,
                    true,
                    $e->promptTokens,
                    $e->completionTokens,
                    $e->totalTokens
                );
            }

            throw $e;
        }
    }

    private function resolveSessionUuid(): ?string
    {
        if ($this->sessionUuid !== null && $this->sessionUuid !== '') {
            return $this->sessionUuid;
        }

        try {
            if (app()->bound('request')) {
                $req = request();
                $header = trim((string) $req->header('X-Consultation-Session', ''));
                if ($header !== '') {
                    return $header;
                }
                if ($req->hasSession()) {
                    $stored = trim((string) $req->session()->get('bhima_consultation_session_uuid', ''));
                    if ($stored !== '') {
                        return $stored;
                    }
                }
            }
        } catch (\Throwable) {
            // Abaikan ketika dijalankan di luar siklus request HTTP
        }

        return null;
    }
}
