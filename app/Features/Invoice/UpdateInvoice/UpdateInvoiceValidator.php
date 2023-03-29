<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

interface UpdateInvoiceValidator
{
    /**
     * @throws ValidationFailed
     */
    public function execute(?array $data, ?BitPayInvoice $bitPayInvoice): void;
}
