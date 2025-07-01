<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

final class BaseUpdateInvoiceValidator implements UpdateInvoiceValidator
{
    public function execute(?array $payload, ?BitPayInvoice $bitPayInvoice, array $headers): void
    {
        if (!$payload) {
            throw new ValidationFailed('Missing data');
        }
    }
}
