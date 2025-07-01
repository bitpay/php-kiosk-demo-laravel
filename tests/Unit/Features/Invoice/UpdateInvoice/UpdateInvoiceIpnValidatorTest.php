<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceIpnValidator;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceValidator;
use App\Features\Invoice\UpdateInvoice\BitPaySignatureValidator;
use App\Features\Shared\Logger;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use BitPaySDK\Model\Invoice\Invoice;
use Tests\Unit\AbstractUnitTestCase;

class UpdateInvoiceIpnValidatorTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_throws_exception_for_missing_bitpay_invoice(): void
    {
        /** @var Logger|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(Logger::class);
        /** @var UpdateInvoiceValidator|\PHPUnit\Framework\MockObject\MockObject $baseUpdateInvoiceValidator */
        $baseUpdateInvoiceValidator = $this->createMock(UpdateInvoiceValidator::class);
        $bitPaySignatureValidator = $this->createBitPaySignatureValidator();

        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger, $bitPaySignatureValidator);

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::MISSING_BITPAY_MESSAGE);
        $logger->expects(self::once())->method('error');

        $class->execute([], null, $this->createValidHeaders([]));
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_id(): void
    {
        /** @var Logger|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(Logger::class);
        /** @var UpdateInvoiceValidator|\PHPUnit\Framework\MockObject\MockObject $baseUpdateInvoiceValidator */
        $baseUpdateInvoiceValidator = $this->createMock(UpdateInvoiceValidator::class);
        $bitPaySignatureValidator = $this->createBitPaySignatureValidator();
        /** @var Invoice|\PHPUnit\Framework\MockObject\MockObject $bitPayInvoice */
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger, $bitPaySignatureValidator);

        $bitPayInvoice->method('getId')->willReturn('5');

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('error');
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::WRONG_BITPAY_INVOICE_ID_MESSAGE);

        $data = ['data' => ['id' => 4]];
        $class->execute($data, $bitPayInvoice, $this->createValidHeaders($data));
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_order_id(): void
    {
        $bitPayId = 4;
        /** @var Logger|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(Logger::class);
        /** @var UpdateInvoiceValidator|\PHPUnit\Framework\MockObject\MockObject $baseUpdateInvoiceValidator */
        $baseUpdateInvoiceValidator = $this->createMock(UpdateInvoiceValidator::class);
        $bitPaySignatureValidator = $this->createBitPaySignatureValidator();
        /** @var Invoice|\PHPUnit\Framework\MockObject\MockObject $bitPayInvoice */
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger, $bitPaySignatureValidator);

        $bitPayInvoice->method('getId')->willReturn((string)$bitPayId);
        $bitPayInvoice->method('getOrderId')->willReturn('invalidOrderId');

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('error');
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::WRONG_BIT_PAY_ORDER_ID_MESSAGE);

        $data = ['data' => ['id' => $bitPayId, 'orderId' => 12]];
        $class->execute($data, $bitPayInvoice, $this->createValidHeaders($data));
    }

    /**
     * @test
     */
    public function it_should_pass_validate(): void
    {
        $bitPayId = 4;
        $bitPayOrderId = 12;
        /** @var Logger|\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(Logger::class);
        /** @var UpdateInvoiceValidator|\PHPUnit\Framework\MockObject\MockObject $baseUpdateInvoiceValidator */
        $baseUpdateInvoiceValidator = $this->createMock(UpdateInvoiceValidator::class);
        $bitPaySignatureValidator = $this->createBitPaySignatureValidator();
        /** @var Invoice|\PHPUnit\Framework\MockObject\MockObject $bitPayInvoice */
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger, $bitPaySignatureValidator);

        $bitPayInvoice->method('getId')->willReturn((string)$bitPayId);
        $bitPayInvoice->method('getOrderId')->willReturn((string)$bitPayOrderId);

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('info');

        $data = ['data' => ['id' => $bitPayId, 'orderId' => $bitPayOrderId]];
        $class->execute($data, $bitPayInvoice, $this->createValidHeaders($data));
    }

    private function createBitPaySignatureValidator(): BitPaySignatureValidator
    {
        /** @var BitPayConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject $bitPayConfig */
        $bitPayConfig = $this->createMock(BitPayConfigurationInterface::class);
        $bitPayConfig->method('getToken')->willReturn('test-token');

        return new BitPaySignatureValidator($bitPayConfig);
    }

    private function createValidHeaders(array $data): array
    {
        $token = 'test-token';
        $signature = base64_encode(hash_hmac('sha256', json_encode($data), $token, true));

        return ['x-signature' => [$signature]];
    }
}
