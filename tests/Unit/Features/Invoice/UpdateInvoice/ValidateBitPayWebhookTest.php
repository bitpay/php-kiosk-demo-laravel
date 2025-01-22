<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Invoice\UpdateInvoice\BitPaySignatureValidator;
use App\Shared\Exceptions\SignatureVerificationFailed;
use Tests\Unit\AbstractUnitTestCase;

class ValidateBitPayWebhookTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_return_error_when_signing_key_is_missing(): void
    {
        $bitpayConfig = $this->createMock(BitPayConfigurationInterface::class);
        $bitpayConfig->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $validator = new BitPaySignatureValidator($bitpayConfig);
        $this->expectException(\RuntimeException::class);
        $validator->execute([], []);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_signature_header_is_missing(): void
    {
        $bitpayConfig = $this->createMock(BitPayConfigurationInterface::class);
        $bitpayConfig->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

        $validator = new BitPaySignatureValidator($bitpayConfig);
        $this->expectException(SignatureVerificationFailed::class);

        $validator->execute([], []);
    }

    /**
     * @test
     */
    public function it_should_return_error_when_signature_does_not_match(): void
    {
        $bitpayConfig = $this->createMock(BitPayConfigurationInterface::class);
        $bitpayConfig->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

        $headers = ['x-signature' => ['invalid-signature']];

        $validator = new BitPaySignatureValidator($bitpayConfig);
        $this->expectException(SignatureVerificationFailed::class);
        $validator->execute([], $headers);
    }

    /**
     * @test
     */
    public function it_should_allow_request_when_signature_is_valid(): void
    {
        $token = 'test-token';
        $testContent = ['test-content'];

        $bitpayConfig = $this->createMock(BitPayConfigurationInterface::class);
        $bitpayConfig->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $expectedSignature = base64_encode(hash_hmac('sha256', json_encode($testContent), $token, true));

        $validator = new BitPaySignatureValidator($bitpayConfig);

        $validator->execute($testContent, ['x-signature' => [$expectedSignature]]);
    }
}
