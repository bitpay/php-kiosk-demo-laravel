<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Features\Shared\SseConfiguration;
use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GetInvoiceViewController extends Controller
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private BitPayConfigurationInterface $bitPayConfiguration;
    private SseConfiguration $sseConfiguration;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        BitPayConfigurationInterface $bitPayConfiguration,
        SseConfiguration $sseConfiguration
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayConfiguration = $bitPayConfiguration;
        $this->sseConfiguration = $sseConfiguration;
    }

    public function execute(Request $request, int $id): View
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->findOne($id);
        if (!$invoice) {
            return view('pages.invoice.missingInvoice', [
                'configuration' => $this->bitPayConfiguration
            ]);
        }

        return view('pages.invoice.invoiceView', [
            'configuration' => $this->bitPayConfiguration,
            'invoice' => $invoice,
            'sseUrl' => $this->sseConfiguration->publicUrl(),
            'sseTopic' => SendUpdateInvoiceNotification::TOPIC
        ]);
    }
}
