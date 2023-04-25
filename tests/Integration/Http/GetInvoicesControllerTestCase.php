<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration\Http;

use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTestCase;

class GetInvoicesControllerTestCase extends IntegrationTestCase
{
    /**
     * @test
     */
    public function it_should_show_invoices_on_grid(): void
    {
        $invoice = ExampleInvoice::createSaved();

        $result = $this->get('/invoices/');
        $result->assertSeeText(ExampleInvoice::BITPAY_ID);
        $result->assertSeeText($invoice->price);
        $result->assertSeeText($invoice->item_description);
        $result->assertSeeText($invoice->status);
    }
}
