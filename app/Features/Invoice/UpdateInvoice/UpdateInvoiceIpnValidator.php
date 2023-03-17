<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Exceptions\ValidationFailed;
use App\Features\Shared\Logger;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

final class UpdateInvoiceIpnValidator implements UpdateInvoiceValidator
{
    private UpdateInvoiceValidator $updateInvoiceValidator;
    private Logger $logger;

    public function __construct(UpdateInvoiceValidator $updateInvoiceValidator, Logger $logger)
    {
        $this->updateInvoiceValidator = $updateInvoiceValidator;
        $this->logger = $logger;
    }

    public function execute(?array $data, ?BitPayInvoice $bitPayInvoice): void
    {
        try {
            $this->updateInvoiceValidator->execute($data, $bitPayInvoice);

            if (!$bitPayInvoice) {
                throw new ValidationFailed('For IPN request, we need original BitPay model');
            }

            $uuid = $data['uuid'] ?? null;
            if (!$uuid) {
                throw new ValidationFailed('Missing uuid');
            }

            $orderId = $data['orderId'] ?? null;
            if (!$orderId || $orderId !== $bitPayInvoice->getOrderId()) {
                throw new ValidationFailed('Wrong order id');
            }

            $this->logger->info('IPN_VALIDATE_SUCCESS', 'Successfully validated IP', ['uuid' => $uuid]);
        } catch (ValidationFailed $e) {
            $this->logger->info('IPN_VALIDATE_FAIL', 'Failed to validate IPN', [
                'errorMessage' => $e->getMessage(),
                'stackTrace' => $e->getTraceAsString()
            ]);
            throw new ValidationFailed($e->getMessage());
        }
    }
}
