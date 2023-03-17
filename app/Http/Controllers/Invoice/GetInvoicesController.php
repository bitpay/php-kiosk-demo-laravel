<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Features\Shared\Logger;
use App\Features\Shared\SseConfiguration;
use App\Http\Controllers\Controller;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GetInvoicesController extends Controller
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

    public function execute(Request $request): View
    {
        $page = $request->get('page') ?? 1;

        $invoices = $this->invoiceRepository->findPaginated((int)$page);

        $this->logger->info('INVOICE_GRID_GET', 'Loaded invoice grid', ['page' => $page]);

        return view('pages.invoice.getInvoices', [
            'configuration' => $this->bitPayConfiguration,
            'invoices' => $invoices,
            'sseUrl' => $this->sseConfiguration->publicUrl(),
            'sseTopic' => SendUpdateInvoiceNotification::TOPIC
        ]);
    }
}
