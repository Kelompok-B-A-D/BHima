<?php

namespace App\Exceptions;

use RuntimeException;

class AnalysisProviderException extends RuntimeException
{
    public function __construct(
        string $userFriendlyMessage,
        public readonly string $errorCode = 'provider_error',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($userFriendlyMessage, $code, $previous);
    }
}
