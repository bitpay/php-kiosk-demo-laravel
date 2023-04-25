<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Models\Invoice\Invoice;

class SendUpdateInvoiceNotification
{
    private SendUpdateInvoiceEventStream $sendUpdateInvoiceEventStream;

    public function __construct(SendUpdateInvoiceEventStream $sendUpdateInvoiceEventStream)
    {
        $this->sendUpdateInvoiceEventStream = $sendUpdateInvoiceEventStream;
    }

    public function execute(Invoice $invoice, ?string $eventName): void
    {
        $this->sendUpdateInvoiceEventStream->execute(
            $invoice,
            $this->getEventMessageTypeFromEventName($eventName),
            $this->getEventMessageFromEventName($invoice->getBitpayId(), $eventName)
        );
    }

    private function getEventMessageTypeFromEventName(?string $eventName): ?UpdateInvoiceEventType
    {
        return match ($eventName) {
            'invoice_paidInFull', 'invoice_confirmed', 'invoice_completed' => UpdateInvoiceEventType::SUCCESS,
            'invoice_expired', 'invoice_failedToConfirm', 'invoice_declined' => UpdateInvoiceEventType::ERROR,
            default => null
        };
    }

    private function getEventMessageFromEventName(string $invoiceId, ?string $eventName): ?string
    {
        return match ($eventName) {
            'invoice_paidInFull' => sprintf('Invoice %s has been paid in full.', $invoiceId),
            'invoice_expired' => sprintf('Invoice %s has expired.', $invoiceId),
            'invoice_confirmed' => sprintf('Invoice %s has been confirmed.', $invoiceId),
            'invoice_completed' => sprintf('Invoice %s is complete.', $invoiceId),
            'invoice_failedToConfirm' => sprintf('Invoice %s has failed to confirm.', $invoiceId),
            'invoice_declined' => sprintf('Invoice %s has been declined.', $invoiceId),
            default => null
        };
    }
}
