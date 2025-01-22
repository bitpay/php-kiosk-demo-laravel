<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\SignatureVerificationFailed;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class BitPaySignatureValidator
{
    public const MISSING_SIGNATURE_MESSAGE = 'Missing signature header';
    public const INVALID_SIGNATURE_MESSAGE = 'Invalid signature';
    public const MISSING_TOKEN_MESSAGE = 'Invalid BitPay configuration - missing token';

    private BitPayConfigurationInterface $bitpayConfiguration;
    public function __construct(
        BitPayConfigurationInterface $bitpayConfiguration,
    ) {
        $this->bitpayConfiguration = $bitpayConfiguration;
    }

    public function execute(array $data, array $headers): void
    {
        $token = $this->bitpayConfiguration->getToken();

        if (!$token) {
            throw new RuntimeException(self::MISSING_TOKEN_MESSAGE);
        }

        $sigHeader = $headers['x-signature'][0] ?? null;

        if (!$sigHeader) {
            throw new SignatureVerificationFailed(self::MISSING_SIGNATURE_MESSAGE);
        }

        $hmac = base64_encode(hash_hmac(
            'sha256',
            json_encode($data),
            $token,
            true
        ));

        if ($sigHeader !== $hmac) {
            throw new SignatureVerificationFailed(self::INVALID_SIGNATURE_MESSAGE);
        }
    }
}
