<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration\Features\Invoice\UpdateInvoice;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceUsingBitPayIpn;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\InvoiceRepositoryInterface;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\PosClient;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use Tests\ExampleInvoice;
use Tests\Integration\IntegrationTestCase;
use App\Features\Invoice\UpdateInvoice\BitPaySignatureValidator;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\Configuration\Design;
use RuntimeException;

class UpdateInvoiceTest extends IntegrationTestCase
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
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::Merchant,
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
            $mock->shouldReceive('getDesign')->andReturn($this->createStub(Design::class));
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        $testedClass = $this->getTestedClass();
        $signature = base64_encode(hash_hmac('sha256', json_encode($data), ExampleInvoice::TOKEN, true));
        $testedClass->execute(ExampleInvoice::UUID, $data, ['x-signature' => [$signature]]);

        $invoice = $this->app->make(InvoiceRepositoryInterface::class)->findOne(1);

        Assert::assertEquals(ExampleInvoice::TOKEN, $invoice->token);
        Assert::assertEquals('someBitpayId', $invoice->bitpay_id);
        Assert::assertEquals('https://test.bitpay.com/invoice?id=MV9fy5iNDkqrg4qrfYpw75', $invoice->bitpay_url);
        // phpcs:disable Generic.Files.LineLength.TooLong
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

    /**
     * @test
     */
    public function it_should_fail_updating_invoice_with_invalid_webhook_signature(): void
    {
        // given
        $fileData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bitPayUpdate.json');
        $data = json_decode($fileData, true, 512, JSON_THROW_ON_ERROR);

        ExampleInvoice::createSaved();

        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::Merchant,
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
            $mock->shouldReceive('execute')->never();
        });

        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(ExampleInvoice::TOKEN);
            $mock->shouldReceive('getDesign')->andReturn($this->createStub(Design::class));
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        $headers = ['x-signature' => ['invalid-signature']];

        $testedClass = $this->getTestedClass();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BitPaySignatureValidator::INVALID_SIGNATURE_MESSAGE);
        $testedClass->execute(ExampleInvoice::UUID, $data, $headers);
    }

        /**
     * @test
     */
    public function it_should_fail_updating_invoice_with_missing_configuration_token(): void
    {
        // given
        $fileData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bitPayUpdate.json');
        $data = json_decode($fileData, true, 512, JSON_THROW_ON_ERROR);

        ExampleInvoice::createSaved();

        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::Merchant,
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
            $mock->shouldReceive('execute')->never();
        });

        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(null);
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        // when
        $testedClass = $this->getTestedClass();

        // then
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BitPaySignatureValidator::MISSING_TOKEN_MESSAGE);
        $testedClass->execute(ExampleInvoice::UUID, $data, [
            'x-signature' => 'signature'
        ]);
    }

    /**
     * @test
     */
    public function it_should_fail_updating_invoice_with_missing_sig_header(): void
    {
        // given
        $fileData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bitPayUpdate.json');
        $data = json_decode($fileData, true, 512, JSON_THROW_ON_ERROR);

        ExampleInvoice::createSaved();

        $this->mock(BitPayClientFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(new class ('', '') extends PosClient {
                public function getInvoice(
                    string $invoiceId,
                    string $facade = Facade::Merchant,
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
            $mock->shouldReceive('execute')->never();
        });

        $this->mock(BitPayConfigurationInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getToken')->andReturn(ExampleInvoice::TOKEN);
            $mock->shouldReceive('getFacade')->andReturn(Facade::MERCHANT);
            $mock->shouldReceive('isSignRequest')->andReturn(true);
        });

        $testedClass = $this->getTestedClass();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(BitPaySignatureValidator::MISSING_SIGNATURE_MESSAGE);
        $testedClass->execute(ExampleInvoice::UUID, $data, []); // Empty headers array
    }

    private function getTestedClass(): UpdateInvoiceUsingBitPayIpn
    {
        return $this->app->make(UpdateInvoiceUsingBitPayIpn::class);
    }
}
