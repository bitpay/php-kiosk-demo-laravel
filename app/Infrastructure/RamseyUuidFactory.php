<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Infrastructure;

use App\Features\Shared\UuidFactory;
use Ramsey\Uuid\Uuid;

class RamseyUuidFactory implements UuidFactory
{
    public function create(): string
    {
        return Uuid::uuid4()->toString();
    }
}
