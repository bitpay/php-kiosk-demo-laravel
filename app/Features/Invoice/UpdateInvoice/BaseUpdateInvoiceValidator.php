<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

final class BaseUpdateInvoiceValidator implements UpdateInvoiceValidator
{
    public function execute(?array $data, ?BitPayInvoice $bitPayInvoice): void
    {
        if (!$data) {
            throw new ValidationFailed('Missing data');
        }
    }
}
