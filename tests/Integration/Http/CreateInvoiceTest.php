<?php

declare(strict_types=1);

namespace Tests\Integration\Http;

use App\Repository\InvoiceRepositoryInterface;
use Tests\Integration\IntegrationTest;

class CreateInvoiceTest extends IntegrationTest
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

        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->bitpay_id);
        $this->assertNotNull($invoice->bitpay_order_id);
        $this->assertEquals(
            '{"store":"store-1","register":"2","reg_transaction_no":"test123","price":"23.54"}',
            $invoice->pos_data_json
        );
        $this->assertEquals(23.54, $invoice->price);
    }
}
