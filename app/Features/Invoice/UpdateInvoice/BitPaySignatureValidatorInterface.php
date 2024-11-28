<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

interface BitPaySignatureValidatorInterface
{
    /**
     * Validates the BitPay signature against the provided data and headers
     *
     * @param array $data The payload data to validate
     * @param array $headers The headers containing the signature - x-signature
     *
     * @throws \App\Shared\Exceptions\SignatureVerificationFailed When signature is missing or invalid
     * @throws \RuntimeException When BitPay token is missing
     */
    public function execute(array $data, array $headers): void;
}
