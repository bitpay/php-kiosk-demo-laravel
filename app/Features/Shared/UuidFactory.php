<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared;

interface UuidFactory
{
    public function create(): string;
}
