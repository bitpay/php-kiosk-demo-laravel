<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use App\Features\Shared\Logger;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

final class UpdateInvoiceIpnValidator implements UpdateInvoiceValidator
{
    public const MISSING_BITPAY_MESSAGE = 'For IPN request, we need original BitPay model';
    public const WRONG_BITPAY_INVOICE_ID_MESSAGE = 'Wrong BitPay id';
    public const WRONG_BIT_PAY_ORDER_ID_MESSAGE = 'Wrong BitPay order id';

    private UpdateInvoiceValidator $updateInvoiceValidator;
    private Logger $logger;
    private BitPaySignatureValidator $bitPaySignatureValidator;

    public function __construct(
        UpdateInvoiceValidator $updateInvoiceValidator,
        Logger $logger,
        BitPaySignatureValidator $bitPaySignatureValidator
    ) {
        $this->updateInvoiceValidator = $updateInvoiceValidator;
        $this->logger = $logger;
        $this->bitPaySignatureValidator = $bitPaySignatureValidator;
    }

    public function execute(?array $data, ?BitPayInvoice $bitPayInvoice, ?array $headers): void
    {
        try {
            $this->bitPaySignatureValidator->execute($data, $headers);
            $this->updateInvoiceValidator->execute($data, $bitPayInvoice, $headers);

            if (!$bitPayInvoice) {
                throw new ValidationFailed(self::MISSING_BITPAY_MESSAGE);
            }

            $bitPayId = $data['id'] ?? null;
            if (!$bitPayId || (string)$bitPayId !== (string)$bitPayInvoice->getId()) {
                throw new ValidationFailed(self::WRONG_BITPAY_INVOICE_ID_MESSAGE);
            }

            $orderId = $data['orderId'] ?? null;
            if (!$orderId || (string)$orderId !== (string)$bitPayInvoice->getOrderId()) {
                throw new ValidationFailed(self::WRONG_BIT_PAY_ORDER_ID_MESSAGE);
            }

            $this->logger->info('IPN_VALIDATE_SUCCESS', 'Successfully validated IP', ['bitpay_id' => $bitPayId]);
        } catch (ValidationFailed $e) {
            $this->logger->error('IPN_VALIDATE_FAIL', 'Failed to validate IPN', [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
