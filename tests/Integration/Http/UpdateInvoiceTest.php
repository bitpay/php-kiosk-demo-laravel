<?php

declare(strict_types=1);

namespace Tests\Integration\Http;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Http\Services\BitPayClientFactory;
use App\Repository\InvoiceRepositoryInterface;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\PosClient;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTest;

class UpdateInvoiceTest extends IntegrationTest
{
    /**
     * @test
     * @throws \JsonException
     */
    public function it_should_throws_404_for_update_invoice_with_non_existing_uuid(): void
    {
        $json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'updateInvoice.json');
        $result = $this->postJson('/invoices/non-existing-uuid', json_decode($json, true, 512, JSON_THROW_ON_ERROR));
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
            $mock->shouldReceive('create')->andReturn(new class('', '') extends PosClient {
                public function getInvoice(string $invoiceId, string $facade = Facade::Merchant, bool $signRequest = true): Invoice
                {
                    return new Invoice();
                }
            });
        });

        // when
        $result = $this->postJson('/invoices/' . $uuid, json_decode($json, true, 512, JSON_THROW_ON_ERROR));

        // then
        $result->assertStatus(Response::HTTP_BAD_REQUEST);
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
            $mock->shouldReceive('create')->andReturn(new class('', '') extends PosClient {
                public function getInvoice(string $invoiceId, string $facade = Facade::Merchant, bool $signRequest = true): Invoice
                {
                    $invoice = new Invoice();
                    $invoice->setId(ExampleInvoice::BITPAY_ID);
                    $invoice->setOrderId(ExampleInvoice::BITPAY_ORDER_ID);

                    return $invoice;
                }
            });
        });

        $this->mock(SendUpdateInvoiceNotification::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')->times(1);
        });

        // when
        $result = $this->postJson('/invoices/' . $uuid, json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        /** @var InvoiceRepositoryInterface $invoiceRepository */
        $invoiceRepository = $this->app->make(InvoiceRepositoryInterface::class);
        $invoice = $invoiceRepository->findOne(1);

        // then
        $result->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertEquals('expired', $invoice->status);
        $this->assertEquals(76.7, $invoice->price);
        $this->assertEquals('https://test.bitpay.com/invoice?id=MV9fy5iNDkqrg4qrfYpw1h', $invoice->bitpay_url); // updated url
        $this->assertEquals('false', $invoice->exception_status);
        $this->assertEquals(0, $invoice->getInvoicePayment()->amount_paid);
    }
}
