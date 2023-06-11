<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

enum Mode: string
{
    case DONATION = "donation";
    case STANDARD = "standard";
}
