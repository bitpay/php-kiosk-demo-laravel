<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Models\Invoice\Invoice;

interface SendUpdateInvoiceEventStream
{
    public const TOPIC = 'update-invoice';

    /**
     * Send update invoice data to event stream.
     *
     * @param Invoice $invoice
     * @param UpdateInvoiceEventType|null $eventType
     * @param string|null $eventMessage
     * @return void
     */
    public function execute(
        Invoice $invoice,
        ?UpdateInvoiceEventType $eventType,
        ?string $eventMessage
    ): void;
}
