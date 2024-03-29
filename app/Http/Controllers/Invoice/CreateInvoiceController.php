<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Features\Invoice\CreateInvoice\CreateInvoice;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ValidationFailed;
use BitpaySDK\Exceptions\BitPayException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Request;

class CreateInvoiceController extends Controller
{
    private CreateInvoice $createInvoice;

    public function __construct(CreateInvoice $createInvoice)
    {
        $this->createInvoice = $createInvoice;
    }

    /**
     * @throws \BitpaySDK\Exceptions\BitPayException
     * @throws \JsonException
     */
    public function execute(Request $request): RedirectResponse
    {
        try {
            $invoice = $this->createInvoice->execute($request->request->all());
        } catch (BitPayException | ValidationFailed $e) {
            return Redirect::route('invoiceForm', ['errorMessage' => $e->getMessage()]);
        }

        return Redirect::to($invoice->bitpay_url);
    }
}
