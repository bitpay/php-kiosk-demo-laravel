<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Models\Invoice\Invoice;

interface SendUpdateInvoiceNotification
{
    public const TOPIC = 'update-invoice';

    public function execute(Invoice $invoice);
}
