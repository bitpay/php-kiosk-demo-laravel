<?php

declare(strict_types=1);

namespace Tests\Integration\Features\Invoice\UpdateInvoice;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceNotification;
use App\Features\Invoice\UpdateInvoice\UpdateInvoice;
use App\Http\Services\BitPayClientFactory;
use App\Repository\InvoiceRepositoryInterface;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\PosClient;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTest;

class UpdateInvoiceTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_update_invoice_and_send_update_notification()
    {
        $fileData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bitPayUpdate.json');
        $data = json_decode($fileData, true, 512, JSON_THROW_ON_ERROR);

        ExampleInvoice::createSaved();

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

        $testedClass = $this->getTestedClass();
        $testedClass->usingBitPayUpdateResponse(ExampleInvoice::UUID, $data);

        $invoice = $this->app->make(InvoiceRepositoryInterface::class)->findOne(1);

        Assert::assertEquals(ExampleInvoice::TOKEN, $invoice->token);
        Assert::assertEquals('someBitpayId', $invoice->bitpay_id);
        Assert::assertEquals('https://test.bitpay.com/invoice?id=MV9fy5iNDkqrg4qrfYpw75', $invoice->bitpay_url);
        Assert::assertEquals("{\"store\":\"store-1\",\"register\":\"2\",\"reg_transaction_no\":\"87678\",\"price\":\"76.70\"}", $invoice->pos_data_json);
        Assert::assertEquals('expired', $invoice->status);
        Assert::assertEquals(76.7, $invoice->price);
        Assert::assertEquals('USD', $invoice->currency_code);
        Assert::assertEquals('false', $invoice->exception_status);

        $eth = $invoice->getInvoicePayment()->paymentCurrencies()->where('currency_code', 'ETH')->first();
        $btc = $invoice->getInvoicePayment()->paymentCurrencies()->where('currency_code', 'BTC')->first();
        Assert::assertEquals(48312000000000000, $eth->total);
        Assert::assertEquals(48312000000000000, $eth->subtotal);
        Assert::assertEquals(347100, $btc->total);
        Assert::assertEquals(342800, $btc->subtotal);
        Assert::assertEquals(0, $invoice->getInvoicePayment()->amount_paid);
        Assert::assertEquals('someBitpayOrderId', $invoice->bitpay_order_id);
    }

    private function getTestedClass(): UpdateInvoice
    {
        return $this->app->make(UpdateInvoice::class);
    }
}
