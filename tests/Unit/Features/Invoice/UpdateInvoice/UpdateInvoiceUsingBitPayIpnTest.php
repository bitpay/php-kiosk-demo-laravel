<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Shared\Exceptions\MissingInvoice;
use App\Features\Invoice\UpdateInvoice\BitPayUpdateMapper;
use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Invoice\UpdateInvoice\UpdatedInvoiceDto;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceEventType;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceUsingBitPayIpn;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceValidator;
use App\Features\Shared\Logger;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;
use App\Models\Invoice\InvoiceRepositoryInterface;
use BitPaySDK\Client;
use Illuminate\Database\Eloquent\Collection;
use Tests\AbstractUnitTest;

class UpdateInvoiceUsingBitPayIpnTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function it_should_throws_exception_for_missing_invoice(): void
    {
        $this->expectException(MissingInvoice::class);
        $repository = $this->getRepository();
        $repository->method('findOneByUuid')->willReturn(null);

        $testedClass = new UpdateInvoiceUsingBitPayIpn(
            $repository,
            $this->getUpdateMapper(),
            $this->getClientFactory(),
            $this->getBitPayConfiguration(),
            $this->getInvoiceValidator(),
            $this->getSendUpdateInvoiceEventStream(),
            $this->getLogger()
        );
        $testedClass->execute('12312', ['any' => 'data']);
    }

    /**
     * @test
     */
    public function it_should_update_invoice_using_bitpay_update_response(): void
    {
        // given
        $repository = $this->getRepository();
        $client = $this->createStub(Client::class);
        $clientFactory = $this->getClientFactory();
        $bitPayConfiguration = $this->getBitPayConfiguration();
        $bitPayUpdateMapper = $this->getUpdateMapper();
        $invoiceValidator = $this->getInvoiceValidator();
        $sendInvoiceEventStream = $this->getSendUpdateInvoiceEventStream();
        $logger = $this->createMock(Logger::class);
        $invoice = $this->getMockBuilder(Invoice::class)->disableOriginalConstructor()->getMock();
        $invoicePayment = $this->getMockBuilder(InvoicePayment::class)->disableOriginalConstructor()->getMock();
        $invoicePaymentData = ['amount_paid' => 5];
        $invoicePaymentCurrency = $this->getMockBuilder(InvoicePaymentCurrency::class)->disableOriginalConstructor()->getMock();
        $invoicePaymentCurrency->method('getCurrencyCode')->willReturn('BTC');
        $invoicePaymentCurrencyBtcData = [
            'currency_code' => 'BTC',
            'total' => 347100,
            'subtotal' => 342800
        ];
        $updatedInvoiceDto = new UpdatedInvoiceDto(
            [
                'invoice_payment' => $invoicePaymentData,
                'invoice_payment_currency' => [$invoicePaymentCurrencyBtcData]
            ],
        );
        $bitPayInvoice = $this->createStub(\BitPaySDK\Model\Invoice\Invoice::class);

        $uuid = 'uuid';
        $bitPayInvoiceId = 'someId';
        $facade = 'pos';
        $repository->method('findOneByUuid')->with($uuid)->willReturn($invoice);
        $clientFactory->method('create')->willReturn($client);
        $invoice->expects(self::exactly(2))->method('getBitpayId')->willReturn($bitPayInvoiceId);
        $invoice->expects(self::exactly(2))->method('getInvoicePayment')->willReturn($invoicePayment);
        $invoicePayment->expects(self::once())->method('fill')->with($invoicePaymentData);
        $invoicePayment->expects(self::once())->method('save');
        $invoicePayment->expects(self::once())->method('getPaymentCurrencies')->willReturn(new Collection([$invoicePaymentCurrency]));
        $invoicePaymentCurrency->expects(self::once())->method('fill')->with($invoicePaymentCurrencyBtcData);
        $invoicePaymentCurrency->expects(self::once())->method('save');
        $bitPayConfiguration->expects(self::once())->method('getFacade')->willReturn($facade . '');
        $bitPayConfiguration->expects(self::once())->method('isSignRequest')->willReturn(false);
        $bitPayUpdateMapper->expects(self::once())->method('execute')->willReturn($updatedInvoiceDto);
        $invoiceValidator->expects(self::once())->method('execute');
        $sendInvoiceEventStream->expects(self::once())->method('execute')
            ->with($invoice, UpdateInvoiceEventType::ERROR, 'Invoice someId has expired.');
        $client->expects(self::once())->method('getInvoice')->with($bitPayInvoiceId, $facade, false)->willReturn($bitPayInvoice);
        $logger->expects(self::once())->method('info')->with('INVOICE_UPDATE_SUCCESS', self::anything(), self::anything());

        $testedClass = new UpdateInvoiceUsingBitPayIpn(
            $repository,
            $bitPayUpdateMapper,
            $clientFactory,
            $bitPayConfiguration,
            $invoiceValidator,
            $sendInvoiceEventStream,
            $logger
        );
        $testedClass->execute($uuid, ['any' => 'data', 'event' => 'invoice_expired']);
    }

    private function getRepository(): InvoiceRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(InvoiceRepositoryInterface::class);
    }

    private function getClientFactory(): \PHPUnit\Framework\MockObject\MockObject|BitPayClientFactory
    {
        return $this->createMock(BitPayClientFactory::class);
    }

    private function getBitPayConfiguration(): \PHPUnit\Framework\MockObject\MockObject|BitPayConfigurationInterface
    {
        return $this->createMock(BitPayConfigurationInterface::class);
    }

    private function getInvoiceValidator(): UpdateInvoiceValidator|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(UpdateInvoiceValidator::class);
    }

    private function getSendUpdateInvoiceEventStream(): SendUpdateInvoiceEventStream|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(SendUpdateInvoiceEventStream::class);
    }

    private function getLogger(): Logger|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(Logger::class);
    }

    private function getUpdateMapper(): \PHPUnit\Framework\MockObject\MockObject|BitPayUpdateMapper
    {
        return $this->createMock(BitPayUpdateMapper::class);
    }
}
