<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\CreateInvoice;

use App\Features\Shared\Configuration\BitPayConfiguration;
use App\Features\Shared\Configuration\Design;
use App\Features\Shared\Configuration\Field;
use App\Features\Shared\Configuration\Hero;
use App\Features\Shared\Configuration\PosData;
use App\Features\Invoice\CreateInvoice\CreateInvoice;
use App\Features\Shared\InvoiceSaver;
use App\Features\Shared\Logger;
use App\Features\Shared\UrlProvider;
use App\Features\Shared\UuidFactory;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use BitPaySDK\Client;
use BitPaySDK\Exceptions\BitPayException;
use Tests\Unit\AbstractUnitTestCase;

class CreateInvoiceTestCase extends AbstractUnitTestCase
{
    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function it_should_throws_exception_for_missing_value_for_required_field(): void
    {
        $hero = $this->createStub(Hero::class);
        $posData = new PosData();
        $posData->setFields([]);
        $design = new Design($hero, 'someLogo', $posData);
        $bitPayConfiguration = new BitPayConfiguration(
            'pos',
            'test',
            $design,
            'someToken',
            'someNotification@email.com'
        );
        $bitPayClientFactory = $this->createMock(BitPayClientFactory::class);
        $invoiceSaver = $this->createMock(InvoiceSaver::class);
        $uuidFactory = $this->createMock(UuidFactory::class);
        $urlProvider = $this->createMock(UrlProvider::class);
        $logger = $this->createMock(Logger::class);

        $createInvoice = new CreateInvoice(
            $bitPayConfiguration,
            $bitPayClientFactory,
            $invoiceSaver,
            $uuidFactory,
            $urlProvider,
            $logger
        );
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123'
        ];

        $this->expectException(\RuntimeException::class);
        $createInvoice->execute($params);
    }

    /**
     * @test
     */
    public function it_should_throws_exception_for_missing_price(): void
    {
        $hero = $this->createStub(Hero::class);
        $priceField = new Field();
        $priceField->setType('price');
        $priceField->setRequired(true);
        $priceField->setName('price');
        $posData = new PosData();
        $posData->setFields([$priceField]);
        $design = new Design($hero, 'someLogo', $posData);
        $bitPayConfiguration = new BitPayConfiguration(
            'pos',
            'test',
            $design,
            'someToken',
            'someNotification@email.com'
        );
        $bitPayClientFactory = $this->createMock(BitPayClientFactory::class);
        $invoiceSaver = $this->createMock(InvoiceSaver::class);
        $uuidFactory = $this->createMock(UuidFactory::class);
        $urlProvider = $this->createMock(UrlProvider::class);
        $logger = $this->createMock(Logger::class);
        $createInvoice = new CreateInvoice(
            $bitPayConfiguration,
            $bitPayClientFactory,
            $invoiceSaver,
            $uuidFactory,
            $urlProvider,
            $logger
        );
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123'
        ];

        $this->expectException(\RuntimeException::class);
        $createInvoice->execute($params);
    }

    /**
     * @test
     */
    public function it_should_log_error_for_invalid_create_invoice_process(): void
    {
        $uuid = 'someUuid';
        $hero = $this->createStub(Hero::class);
        $priceField = new Field();
        $priceField->setType('price');
        $priceField->setRequired(true);
        $priceField->setName('price');
        $posData = new PosData();
        $posData->setFields([$priceField]);
        $design = new Design($hero, 'someLogo', $posData);
        $bitPayConfiguration = new BitPayConfiguration(
            'pos',
            'test',
            $design,
            'someToken',
            'someNotification@email.com'
        );
        $bitPayClient = $this->createMock(Client::class);
        $bitPayClientFactory = $this->createMock(BitPayClientFactory::class);
        $invoiceSaver = $this->createMock(InvoiceSaver::class);
        $uuidFactory = $this->createMock(UuidFactory::class);
        $urlProvider = $this->createMock(UrlProvider::class);
        $logger = $this->createMock(Logger::class);
        $createInvoice = new CreateInvoice(
            $bitPayConfiguration,
            $bitPayClientFactory,
            $invoiceSaver,
            $uuidFactory,
            $urlProvider,
            $logger
        );
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'price' => '23.54'
        ];

        $urlProvider->method('applicationUrl')->willReturn('http://localhost');
        $uuidFactory->method('create')->willReturn($uuid);
        $bitPayClientFactory->method('create')->willReturn($bitPayClient);
        $bitPayClient->expects(self::once())->method('createInvoice')->willThrowException(new BitPayException());
        $logger->expects(self::once())->method('error');
        $this->expectException(\RuntimeException::class);

        $createInvoice->execute($params);
    }

    /**
     * @test
     */
    public function it_should_create_invoice(): void
    {
        $uuid = 'someUuid';
        $hero = $this->createStub(Hero::class);
        $priceField = new Field();
        $priceField->setType('price');
        $priceField->setRequired(true);
        $priceField->setName('price');
        $posData = new PosData();
        $posData->setFields([$priceField]);
        $design = new Design($hero, 'someLogo', $posData);
        $bitPayConfiguration = new BitPayConfiguration(
            'pos',
            'test',
            $design,
            'someToken',
            'someNotification@email.com'
        );
        $bitPayClient = $this->createMock(Client::class);
        $bitPayClientFactory = $this->createMock(BitPayClientFactory::class);
        $invoiceSaver = $this->createMock(InvoiceSaver::class);
        $uuidFactory = $this->createMock(UuidFactory::class);
        $urlProvider = $this->createMock(UrlProvider::class);
        $logger = $this->createMock(Logger::class);
        $bitPayInvoice = $this->createStub(\BitPaySDK\Model\Invoice\Invoice::class);
        $appInvoice = $this->createStub(Invoice::class);

        $urlProvider->method('applicationUrl')->willReturn('http://localhost');
        $uuidFactory->method('create')->willReturn($uuid);
        $bitPayClientFactory->method('create')->willReturn($bitPayClient);
        $bitPayClient->expects(self::once())->method('createInvoice')->willReturn($bitPayInvoice);
        $invoiceSaver->expects(self::once())->method('fromSdkModel')->with($bitPayInvoice, $uuid)
            ->willReturn($appInvoice);
        $logger->expects(self::once())->method('info');

        $createInvoice = new CreateInvoice(
            $bitPayConfiguration,
            $bitPayClientFactory,
            $invoiceSaver,
            $uuidFactory,
            $urlProvider,
            $logger
        );
        $params = [
            'store' => 'store-1',
            'register' => '2',
            'reg_transaction_no' => 'test123',
            'price' => '23.54'
        ];
        $result = $createInvoice->execute($params);
        self::assertEquals($appInvoice, $result);
    }
}
