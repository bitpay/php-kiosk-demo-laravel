<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Infrastructure\Laravel;

use App\Features\Shared\Logger;

class LaravelLogger implements Logger
{
    private \Illuminate\Log\Logger $logger;

    public function __construct(\Illuminate\Log\Logger $logger)
    {
        $this->logger = $logger;
    }

    public function info(
        string $code,
        string $message,
        array $context
    ): void {
        $this->logger->info($this->jsonFromInput('INFO', $code, $message, $context));
    }

    public function error(string $code, string $message, array $context): void
    {
        $this->logger->info($this->jsonFromInput('ERROR', $code, $message, $context));
    }

    private function jsonFromInput(
        string $level,
        string $code,
        string $message,
        array $context
    ): string {
        return json_encode([
            'level' => $level,
            'timestamp' => time(),
            'code' => $code,
            'message' => $message,
            'context' => $context
        ], JSON_THROW_ON_ERROR);
    }
}
