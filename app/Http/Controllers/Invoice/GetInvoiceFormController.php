<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Configuration\BitPayConfigurationFactoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class GetInvoiceFormController extends Controller
{
    private BitPayConfigurationFactoryInterface $bitPayConfigurationFactory;

    public function __construct(BitPayConfigurationFactoryInterface $bitPayConfigurationFactory)
    {
        $this->bitPayConfigurationFactory = $bitPayConfigurationFactory;
    }

    public function execute(): View|Factory
    {
        return view('invoiceForm', [
            'configuration' => $this->bitPayConfigurationFactory->create(),
            'errorMessage' => null
        ]);
    }
}
