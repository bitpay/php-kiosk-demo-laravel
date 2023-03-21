<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Features\Shared\Logger;
use App\Features\Shared\Logger\LogCode;
use App\Features\Shared\SseConfiguration;
use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetInvoiceViewController extends Controller
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private BitPayConfigurationInterface $bitPayConfiguration;
    private SseConfiguration $sseConfiguration;
    private Logger $logger;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        BitPayConfigurationInterface $bitPayConfiguration,
        SseConfiguration $sseConfiguration,
        Logger $logger
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayConfiguration = $bitPayConfiguration;
        $this->sseConfiguration = $sseConfiguration;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return View
     * @deprecated
     */
    public function execute(Request $request, int $id): View
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->findOne($id);
        if (!$invoice) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $this->logger->info('INVOICE_GET', 'Loaded invoice', ['id' => $id]);

        return view('pages.invoice.invoiceView', [
            'configuration' => $this->bitPayConfiguration,
            'invoice' => $invoice,
            'sseUrl' => $this->sseConfiguration->publicUrl(),
            'sseTopic' => SendUpdateInvoiceNotification::TOPIC
        ]);
    }

    private function a() {

    }
}
