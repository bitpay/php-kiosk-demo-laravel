<?php

declare(strict_types=1);

namespace App\Infrastructure\Mercure;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Models\Invoice\Invoice;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SendMercureUpdateInvoiceNotification implements SendUpdateInvoiceNotification
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    /**
     * @throws \JsonException
     */
    public function execute(Invoice $invoice): void
    {
        $this->hub->publish(new Update(
            'update-invoice',
            json_encode(
                [
                    'status' => $invoice->status,
                    'uuid' => $invoice->uuid
                ],
                JSON_THROW_ON_ERROR
            )
        ));
    }
}
