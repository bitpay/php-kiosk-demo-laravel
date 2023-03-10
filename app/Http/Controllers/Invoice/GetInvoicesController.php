<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Http\Controllers\Controller;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Http\Request;

class GetInvoicesController extends Controller
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private BitPayConfigurationInterface $bitPayConfiguration;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository, BitPayConfigurationInterface $bitPayConfiguration)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function execute(Request $request)
    {
        $page = $request->get('page') ?? 1;

        $invoices = $this->invoiceRepository->findPaginated((int)$page);

        return view('pages.invoice.getInvoices', [
            'configuration' => $this->bitPayConfiguration,
            'invoices' => $invoices
        ]);
    }
}
