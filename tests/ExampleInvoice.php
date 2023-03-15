<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceBuyer;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;

class ExampleInvoice
{
    public const UUID = '04373e9d-d265-4a07-aea4-c8a67c253968';
    public const TOKEN = 'someToken';

    public static function create(): Invoice
    {
        $invoice = new Invoice();
        $invoice->uuid = self::UUID;
        $invoice->price = 12.35;
        $invoice->token = self::TOKEN;

        $invoiceBuyer = new InvoiceBuyer([
            'name' => 'SomeName',
            'address1' => 'SomeAddress'
        ]);

        $invoicePayment = new InvoicePayment([
            'amount_paid' => 1
        ]);
        $invoicePaymentCurrency = new InvoicePaymentCurrency([
            'currency_code' => 'BTC',
            'total' => 0.25,
            'subtotal' => 0.25,
            'display_total' => 0.25,
            'display_subtotal' => 0.25,
        ]);
        $invoicePaymentCurrency->invoicePayment()->associate($invoicePaymentCurrency);
        $invoice->invoicePayment()->associate($invoicePayment);
        $invoice->invoiceBuyer()->associate($invoiceBuyer);

        return $invoice;
    }

    public static function createSaved(): Invoice
    {
        $invoice = self::create();

        $invoicePayment = $invoice->getInvoicePayment();
        $invoicePayment->save();
        $invoice->invoiceBuyer()->associate($invoice->getInvoiceBuyer()->save());

        /** @var InvoicePaymentCurrency $paymentCurrency */
        foreach ($invoicePayment->getPaymentCurrencies() as $paymentCurrency) {
            $paymentCurrency->save();
        }

        $invoice->invoicePayment()->associate($invoicePayment);

        $invoice->save();

        return $invoice;
    }
}
