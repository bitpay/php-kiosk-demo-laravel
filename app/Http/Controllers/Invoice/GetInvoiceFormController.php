<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class GetInvoiceFormController extends Controller
{
    private BitPayConfigurationInterface $bitPayConfiguration;

    public function __construct(BitPayConfigurationInterface $bitPayConfiguration)
    {
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function execute(): View|Factory
    {
        return view('pages.invoice.createInvoiceForm', [
            'configuration' => $this->bitPayConfiguration,
            'errorMessage' => null
        ]);
    }
}
