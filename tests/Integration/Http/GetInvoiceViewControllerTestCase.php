<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration\Http;

use Symfony\Component\HttpFoundation\Response;
use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTestCase;

class GetInvoiceViewControllerTestCase extends IntegrationTestCase
{
    /**
     * @test
     */
    public function it_should_show_invoice_view(): void
    {
        $invoice = ExampleInvoice::createSaved();

        $result = $this->get('/invoices/' . $invoice->id);
        $result->assertSeeText(ExampleInvoice::BITPAY_ID);
        $result->assertSeeText($invoice->price);
        $result->assertSeeText($invoice->bitpay_order_id);
        $result->assertSeeText($invoice->status);
    }

    /**
     * @test
     */
    public function it_should_return_404_for_non_existing_invoice(): void
    {
        $result = $this->get('/invoices/1');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());
    }
}
