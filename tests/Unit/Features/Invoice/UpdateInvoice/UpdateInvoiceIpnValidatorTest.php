<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceIpnValidator;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceValidator;
use App\Features\Shared\Logger;
use BitPaySDK\Model\Invoice\Invoice;
use Tests\AbstractUnitTest;

class UpdateInvoiceIpnValidatorTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function it_should_throws_exception_for_missing_bitpay_invoice(): void
    {
        $logger = $this->createStub(Logger::class);
        $baseUpdateInvoiceValidator = $this->createStub(UpdateInvoiceValidator::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger);

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::MISSING_BITPAY_MESSAGE);
        $logger->expects(self::once())->method('error');

        $class->execute([], null);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_id(): void
    {
        $logger = $this->createStub(Logger::class);
        $baseUpdateInvoiceValidator = $this->createStub(UpdateInvoiceValidator::class);
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger);

        $bitPayInvoice->method('getId')->willReturn('5');

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('error');
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::WRONG_BITPAY_INVOICE_ID_MESSAGE);

        $class->execute(['id' => 4], $bitPayInvoice);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_order_id(): void
    {
        $bitPayId = 4;
        $logger = $this->createStub(Logger::class);
        $baseUpdateInvoiceValidator = $this->createStub(UpdateInvoiceValidator::class);
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger);

        $bitPayInvoice->method('getId')->willReturn((string)$bitPayId);
        $bitPayInvoice->method('getOrderId')->willReturn('invalidOrderId');

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('error');
        $this->expectExceptionMessage(UpdateInvoiceIpnValidator::WRONG_BIT_PAY_ORDER_ID_MESSAGE);

        $class->execute(['id' => $bitPayId, 'orderId' => 12], $bitPayInvoice);
    }

    /**
     * @test
     */
    public function it_should_pass_validate(): void
    {
        $bitPayId = 4;
        $bitPayOrderId = 12;
        $logger = $this->createMock(Logger::class);
        $baseUpdateInvoiceValidator = $this->createStub(UpdateInvoiceValidator::class);
        $bitPayInvoice = $this->createMock(Invoice::class);
        $class = new UpdateInvoiceIpnValidator($baseUpdateInvoiceValidator, $logger);

        $bitPayInvoice->method('getId')->willReturn((string)$bitPayId);
        $bitPayInvoice->method('getOrderId')->willReturn((string)$bitPayOrderId);

        $baseUpdateInvoiceValidator->expects(self::once())->method('execute');
        $logger->expects(self::once())->method('info');

        $class->execute(['id' => $bitPayId, 'orderId' => $bitPayOrderId], $bitPayInvoice);
    }
}
