<?php

declare(strict_types=1);

namespace App\Infrastructure\Mercure;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceEventType;
use App\Models\Invoice\Invoice;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class SendMercureUpdateInvoiceEventStream implements SendUpdateInvoiceEventStream
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    /**
     * @throws \JsonException
     */
    public function execute(
        Invoice $invoice,
        ?UpdateInvoiceEventType $eventType,
        ?string $eventMessage
    ): void {
        $this->hub->publish(new Update(
            'update-invoice',
            json_encode(
                [
                    'status' => $invoice->status,
                    'uuid' => $invoice->uuid,
                    'eventType' => strtolower($eventType->name),
                    'eventMessage' => $eventMessage
                ],
                JSON_THROW_ON_ERROR
            )
        ));
    }
}
