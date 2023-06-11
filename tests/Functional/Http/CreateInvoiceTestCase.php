<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Functional\Http;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\Configuration\Mode;
use App\Models\Invoice\InvoiceRepositoryInterface;
use Tests\Functional\AbstractFunctionalTestCase;

class CreateInvoiceTestCase extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function it_should_fill_form_and_create_standard_bitpay_invoice(): void
    {
        /** @var BitPayConfigurationInterface $configuration */
        $configuration = $this->app->get(BitPayConfigurationInterface::class);
        $configuration->setMode(Mode::STANDARD);

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

    /**
     * @test
     */
    public function it_should_fill_form_and_create_donation_bitpay_invoice(): void
    {
        /** @var BitPayConfigurationInterface $configuration */
        $configuration = $this->app->get(BitPayConfigurationInterface::class);
        $configuration->setMode(Mode::DONATION);

        $expectedName = 'Test';
        $expectedPhone = '997';

        $this->post('/invoices', [
            'buyerName' => $expectedName,
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => $expectedPhone,
            'buyerEmail' => 'some@email.com',
            '_token' => 'FsBmW4CMKpw8zjEBO9F3TQu9tGBbcTJkPQPKcGv7',
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'price' => '23.54'
        ]);

        /** @var InvoiceRepositoryInterface $invoiceRepository */
        $invoiceRepository = $this->app->make(InvoiceRepositoryInterface::class);
        $invoice = $invoiceRepository->findOne(1);
        $invoiceBuyerProvidedInfo = $invoice->getInvoiceBuyer()->getInvoiceBuyerProvidedInfo();

        self::assertNotNull($invoice);
        self::assertNotNull($invoice->bitpay_id);
        self::assertNotNull($invoice->bitpay_order_id);
        // @codingStandardsIgnoreStart
        self::assertEquals(
            '{"store":"store-1","register":"2","reg_transaction_no":"test123","price":"23.54","buyerName":"Test","buyerAddress1":"SomeTestAddress","buyerAddress2":null,"buyerLocality":"SomeCity","buyerRegion":"AK","buyerPostalCode":"12345","buyerPhone":"997","buyerEmail":"some@email.com"}',
            $invoice->pos_data_json
        );
        // @codingStandardsIgnoreEnd
        self::assertEquals(23.54, $invoice->price);
        self::assertEquals($expectedName, $invoiceBuyerProvidedInfo->name);
        self::assertEquals($expectedPhone, $invoiceBuyerProvidedInfo->phone_number);
    }
}
