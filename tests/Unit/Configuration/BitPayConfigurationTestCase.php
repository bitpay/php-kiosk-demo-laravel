<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Configuration;

use App\Features\Shared\Configuration\BitPayConfiguration;
use App\Features\Shared\Configuration\Design;
use App\Features\Shared\Configuration\Field;
use App\Features\Shared\Configuration\PosData;
use Tests\Unit\AbstractUnitTestCase;

class BitPayConfigurationTestCase extends AbstractUnitTestCase
{
    private const TOKEN = 'someToken';
    private const ENV = 'prod';
    private const FACADE = 'merchant';
    private const NOTIFICATION_EMAIL = 'some@email.com';

    /**
     * @test
     */
    public function it_should_return_environemnt(): void
    {
        $testedClass = $this->getTestedClass();
        self::assertEquals(self::ENV, $testedClass->getEnvironment());
    }

    /**
     * @test
     */
    public function it_should_return_default_environemnt(): void
    {
        $testedClass = new BitPayConfiguration(
            self::FACADE,
            null,
            $this->getDesign(),
            null,
            null
        );
        self::assertEquals('test', $testedClass->getEnvironment());
    }

    /**
     * @test
     */
    public function it_should_return_facade(): void
    {
        $testedClass = $this->getTestedClass();
        self::assertEquals(self::FACADE, $testedClass->getFacade());
    }

    /**
     * @test
     */
    public function it_should_return_default_facade(): void
    {
        $testedClass = new BitPayConfiguration(
            null,
            null,
            null,
            null,
            null
        );
        self::assertEquals('pos', $testedClass->getFacade());
    }

    /**
     * @test
     */
    public function it_should_return_design(): void
    {
        $design = $this->getDesign();
        $testedClass = $this->getTestedClass($design);
        self::assertEquals($design, $testedClass->getDesign());
    }

    /**
     * @test
     */
    public function it_should_return_token(): void
    {
        $testedClass = $this->getTestedClass();
        self::assertEquals(self::TOKEN, $testedClass->getToken());
    }

    /**
     * @test
     */
    public function it_should_return_notification_email(): void
    {
        $testedClass = $this->getTestedClass();
        self::assertEquals(self::NOTIFICATION_EMAIL, $testedClass->getNotificationEmail());
    }

    /**
     * @test
     */
    public function it_should_return_is_sign_request(): void
    {
        $testedClass = new BitPayConfiguration(
            'pos',
            null,
            null,
            null,
            null
        );
        $this->assertEquals(false, $testedClass->isSignRequest());

        $testedClass = new BitPayConfiguration(
            'merchant',
            null,
            null,
            null,
            null
        );
        $this->assertEquals(true, $testedClass->isSignRequest());
    }

    /**
     * @test
     */
    public function it_should_return_iso_code(): void
    {
        // given
        $field1 = new Field();
        $field1->setType('someType');
        $field2 = new Field();
        $field2->setType('price');
        $currency = 'PLN';
        $field2->setCurrency($currency);

        $posData = $this->createStub(PosData::class);
        $posData->method('getFields')->willReturn([
            $field1,
            $field2
        ]);
        $design = $this->createStub(Design::class);
        $design->method('getPosData')->willReturn($posData);

        $testedClass = new BitPayConfiguration(
            self::FACADE,
            self::ENV,
            $design,
            self::TOKEN,
            self::NOTIFICATION_EMAIL
        );

        // when
        $result = $testedClass->getCurrencyIsoCode();

        // then
        $this->assertEquals($currency, $result);
    }

    /**
     * @test
     */
    public function it_should_return_usd_as_default_currency(): void
    {
        // given
        $posData = $this->createStub(PosData::class);
        $posData->method('getFields')->willReturn([]);
        $design = $this->createStub(Design::class);
        $design->method('getPosData')->willReturn($posData);

        $testedClass = new BitPayConfiguration(
            self::FACADE,
            self::ENV,
            $design,
            self::TOKEN,
            self::NOTIFICATION_EMAIL
        );

        // when
        $result = $testedClass->getCurrencyIsoCode();

        // then
        $this->assertEquals('USD', $result);
    }

    private function getTestedClass(?Design $design = null): BitPayConfiguration
    {
        if (!$design) {
            $design = $this->getDesign();
        }

        return new BitPayConfiguration(
            self::FACADE,
            self::ENV,
            $design,
            self::TOKEN,
            self::NOTIFICATION_EMAIL
        );
    }

    /**
     * @return Design|\PHPUnit\Framework\MockObject\MockObject
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getDesign(): Design|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(Design::class);
    }
}
