<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Functional\Http;

use App\Models\Invoice\InvoiceRepositoryInterface;
use Tests\Functional\AbstractFunctionalTest;

class CreateInvoiceTest extends AbstractFunctionalTest
{
    /**
     * @test
     */
    public function it_should_fill_form_and_create_bitpay_invoice(): void
    {
        $this->post('/invoices', [
            '_token' => 'FsBmW4CMKpw8zjEBO9F3TQu9tGBbcTJkPQPKcGv7',
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'price' => '23.54'
        ]);

        /** @var InvoiceRepositoryInterface $invoiceRepository */
        $invoiceRepository = $this->app->make(InvoiceRepositoryInterface::class);
        $invoice = $invoiceRepository->findOne(1);

        self::assertNotNull($invoice);
        self::assertNotNull($invoice->bitpay_id);
        self::assertNotNull($invoice->bitpay_order_id);
        self::assertEquals(
            '{"store":"store-1","register":"2","reg_transaction_no":"test123","price":"23.54"}',
            $invoice->pos_data_json
        );
        self::assertEquals(23.54, $invoice->price);
    }
}
