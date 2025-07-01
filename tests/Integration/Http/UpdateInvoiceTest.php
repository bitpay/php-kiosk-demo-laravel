<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration\Http;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Shared\BitPayClientFactory;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Models\Invoice\InvoiceRepositoryInterface;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\PosClient;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTestCase;

class UpdateInvoiceTest extends IntegrationTestCase
{
    /**
     * @test
     * @throws \JsonException
     */
    public function it_should_throws_404_for_update_invoice_with_non_existing_uuid(): void
    {
        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(ExampleInvoice::TOKEN);
        });

        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'updateInvoice.json');
        $jsonData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', json_encode($jsonData['data']), ExampleInvoice::TOKEN, true));
        $result = $this->postJson('/invoices/non-existing-uuid', $jsonData, ['X-Signature' => $signature]);
        $result->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function it_should_not_update_invoice_for_invalid_bitpay_order_id(): void
    {
        // given
        $invoice = ExampleInvoice::createSaved();
        $invoice->bitpay_order_id = 'someInvalidId';
        $invoice->save();

        $uuid = $invoice->uuid;
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'updateInvoice.json');

        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::MERCHANT,
                    bool $signRequest = true
                ): Invoice {
                    return new Invoice();
                }
            });
        });

        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(ExampleInvoice::TOKEN);
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        // when
        $jsonData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', json_encode($jsonData['data']), ExampleInvoice::TOKEN, true));
        $result = $this->postJson('/invoices/' . $uuid, $jsonData, ['X-Signature' => $signature]);

        // then
        $result->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function it_should_update_invoice(): void
    {
        // given
        $invoice = ExampleInvoice::createSaved();
        $invoice->save();

        $uuid = $invoice->uuid;
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'updateInvoice.json');

        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::MERCHANT,
                    bool $signRequest = true
                ): Invoice {
                    $invoice = new Invoice();
                    $invoice->setId(ExampleInvoice::BITPAY_ID);
                    $invoice->setOrderId(ExampleInvoice::BITPAY_ORDER_ID);

                    return $invoice;
                }
            });
        });

        $this->mock(SendUpdateInvoiceEventStream::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')->times(1);
        });

        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(ExampleInvoice::TOKEN);
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        // when
        $jsonData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', json_encode($jsonData), ExampleInvoice::TOKEN, true));
        $result = $this->postJson('/invoices/' . $uuid, $jsonData, ['X-Signature' => $signature]);
        /** @var InvoiceRepositoryInterface $invoiceRepository */
        $invoiceRepository = $this->app->make(InvoiceRepositoryInterface::class);
        $invoice = $invoiceRepository->findOne(1);

        // then
        $result->assertStatus(Response::HTTP_NO_CONTENT);
        self::assertEquals('expired', $invoice->status);
        self::assertEquals(76.7, $invoice->price);
        self::assertEquals(
            'https://test.bitpay.com/invoice?id=someBitpayId',
            $invoice->bitpay_url
        ); // updated url
        self::assertEquals('false', $invoice->exception_status);
        self::assertEquals(0, $invoice->getInvoicePayment()->amount_paid);
    }
}
