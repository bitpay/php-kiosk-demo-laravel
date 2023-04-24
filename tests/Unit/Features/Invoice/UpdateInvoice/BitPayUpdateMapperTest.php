<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Features\Invoice\UpdateInvoice\BitPayUpdateMapper;
use App\Features\Shared\DateTimeImmutableCreator;
use App\Infrastructure\StringConverter;
use Tests\TestCase;

class BitPayUpdateMapperTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_map_bitpay_response_to_application_invoice_fields()
    {
        $fileData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'bitPayUpdate.json');
        $data = json_decode($fileData, true, 512, JSON_THROW_ON_ERROR);

        $mappedData = $this->getTestedClass()->execute($data);

        $expected = [
            'bitpay_id' => 'MV9fy5iNDkqrg4qrfYpw75',
            'bitpay_url' => 'https://test.bitpay.com/invoice?id=MV9fy5iNDkqrg4qrfYpw75',
            'pos_data_json' => '{"store":"store-1","register":"2","reg_transaction_no":"87678","price":"76.70"}',
            'status' => 'expired',
            'price' => 76.7,
            'currency_code' => 'USD',
            'expiration_time' => DateTimeImmutableCreator::fromTimestamp(1678715559517),
            'exception_status' => 'false',
            'invoice_payment' => ['amount_paid' => 0],
            'invoice_payment_currency' => [
                [
                    'currency_code' => 'BTC',
                    'total' => 347100,
                    'subtotal' => 342800,
                ],
                [
                    'currency_code' => 'BCH',
                    'total' => 62648000,
                    'subtotal' => 62648000,
                ],
                [
                    'currency_code' => 'ETH',
                    'total' => 48312000000000000,
                    'subtotal' => 48312000000000000,
                ],
                [
                    'currency_code' => 'GUSD',
                    'total' => 7670,
                    'subtotal' => 7670,
                ],
                [
                    'currency_code' => 'PAX',
                    'total' => 76700000000000000000,
                    'subtotal' => 76700000000000000000,
                ],
                [
                    'currency_code' => 'BUSD',
                    'total' => 76700000000000000000,
                    'subtotal' => 76700000000000000000,
                ],
                [
                    'currency_code' => 'USDC',
                    'total' => 76700000,
                    'subtotal' => 76700000,
                ],
                [
                    'currency_code' => 'DOGE',
                    'total' => 111086650500,
                    'subtotal' => 111086650500,
                ],
                [
                    'currency_code' => 'LTC',
                    'total' => 101107300,
                    'subtotal' => 101107300,
                ],
                [
                    'currency_code' => 'MATIC',
                    'total' => 69057493000000000000,
                    'subtotal' => 69057493000000000000,
                ],
                [
                    'currency_code' => 'USDC_m',
                    'total' => 76700000,
                    'subtotal' => 76700000,
                ],
                [
                    'currency_code' => 'USDT',
                    'total' => 76500000,
                    'subtotal' => 76500000,
                ]
            ],
            'bitpay_order_id' => '640f27154e58f8.40716035',
            'created_date' => DateTimeImmutableCreator::fromTimestamp(1678714659517),
            'buyer_fields' => [],
            'exchange_rates' => [],
        ];
        self::assertEquals($expected, $mappedData->toArray());
    }

    private function getTestedClass(): BitPayUpdateMapper
    {
        return new BitPayUpdateMapper(new StringConverter());
    }
}
