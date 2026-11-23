<?php

namespace App\Exceptions;

use RuntimeException;

class AnalysisProviderException extends RuntimeException
{
    public function __construct(
        string $userFriendlyMessage,
        public readonly string $errorCode = 'provider_error',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly int $promptTokens = 0,
        public readonly int $completionTokens = 0,
        public readonly int $totalTokens = 0,
    ) {
        parent::__construct($userFriendlyMessage, $code, $previous);
    }
}
