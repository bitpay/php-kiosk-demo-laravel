<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Http\Request;

class GetInvoiceViewController extends Controller
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private BitPayConfigurationInterface $bitPayConfiguration;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository, BitPayConfigurationInterface $bitPayConfiguration)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function execute(Request $request, int $id)
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
            'invoice' => $invoice
        ]);
    }
}
