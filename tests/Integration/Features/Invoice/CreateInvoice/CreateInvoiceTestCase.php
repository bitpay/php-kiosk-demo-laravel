<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration\Features\Invoice\CreateInvoice;

use App\Features\Invoice\CreateInvoice\CreateInvoice;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\InvoiceRepositoryInterface;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\PosClient;
use Mockery\MockInterface;
use Tests\ExampleInvoice;
use Tests\Integration\ExampleSdkInvoice;
use Tests\Integration\IntegrationTestCase;

class CreateInvoiceTestCase extends IntegrationTestCase
{
    /**
     * @test
     */
    public function it_should_create_kiosk_invoice(): void
    {
        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function createInvoice(
                    Invoice $invoice,
                    string $facade = Facade::Merchant,
                    bool $signRequest = true
                ): Invoice {
                    return ExampleSdkInvoice::create();
                }

                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::Merchant,
                    bool $signRequest = true
                ): Invoice {
                    return ExampleSdkInvoice::create();
                }
            });
        });

        $testedClass = $this->getTestedClass();
        $testedClass->execute([
            '_token' => 'FsBmW4CMKpw8zjEBO9F3TQu9tGBbcTJkPQPKcGv7',
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'price' => 23.54
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

    private function getTestedClass(): CreateInvoice
    {
        return $this->app->make(CreateInvoice::class);
    }
}
