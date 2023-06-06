<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\CreateInvoice\Validator;

use App\Features\Invoice\CreateInvoice\Validator\DonationParamsValidator;
use App\Features\Invoice\CreateInvoice\Validator\PosParamsValidator;
use App\Shared\Exceptions\ValidationFailed;
use Tests\Unit\AbstractUnitTestCase;

class DonationParamsValidatorTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_name(): void
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => null,
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_address(): void
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => null,
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_locality(): void
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => null,
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_region(): void
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'XX',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_postal_code()
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345789',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_phone()
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => null,
            'buyerEmail' => 'some@email.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_invalid_email()
    {
        $this->expectException(ValidationFailed::class);
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'someemail.com',
        ];

        $testedClass->execute($params);
    }

    /**
     * @test
     */
    public function it_should_returns_validated_params(): void
    {
        $testedClass = $this->getTestedClass();
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'buyerName' => 'Test',
            'buyerAddress1' => 'SomeTestAddress',
            'buyerAddress2' => null,
            'buyerLocality' => 'SomeCity',
            'buyerRegion' => 'AK',
            'buyerPostalCode' => '12345',
            'buyerPhone' => '997',
            'buyerEmail' => 'some@email.com',
        ];

        $result = $testedClass->execute($params);

        self::assertEquals($params, $result);
    }

    private function getTestedClass(): DonationParamsValidator
    {
        $validator = $this->createMock(PosParamsValidator::class);
        $validator->method('execute')->willReturn([
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123']
        );

        return new DonationParamsValidator($validator);
    }
}
