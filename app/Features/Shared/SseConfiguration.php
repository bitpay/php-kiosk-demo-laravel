<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared;

interface SseConfiguration
{
    public function publicUrl(): string;

    public function internalUrl(): string;
}
