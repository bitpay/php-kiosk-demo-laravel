<?php

declare(strict_types=1);

namespace App\Features\Shared;

interface Logger
{
    public function info(string $code, string $message, array $context): void;

    public function error(string $code, string $message, array $context): void;
}
