<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Features\Shared\DateTimeImmutableCreator;
use App\Features\Shared\StringConverter;

class BitPayUpdateMapper
{
    private StringConverter $stringConverter;

    public function __construct(StringConverter $stringConverter)
    {
        $this->stringConverter = $stringConverter;
    }

    /**
     * This method converts response from BitPay with updated Invoice
     */
    public function execute(array $data): UpdatedInvoiceDto
    {
        $data = $this->convertCamelCaseToSnakeCase($data);
        $this->renameFields($data);
        $this->addDateToFields($data);
        $this->addInvoicePaymentCurrencies($data);
        $this->mapToSpecificType($data);
        $this->changeStructure($data);
        $this->removeUnusedFields($data);

        return new UpdatedInvoiceDto($data);
    }

    private function convertCamelCaseToSnakeCase(array $data): array
    {
        $excludedForSnakeCase = ['paymentSubtotals', 'paymentTotals'];
        return $this->stringConverter->toSnakeCaseArray($data, $excludedForSnakeCase);
    }

    private function addInvoicePaymentCurrencies(array &$data): void
    {
        $paymentTotals = $data['paymentTotals'] ?? null;
        $paymentSubtotals = $data['paymentSubtotals'] ?? null;
        if (!$paymentTotals) {
            return;
        }

        $invoicePaymentCurrencies = [];
        foreach ($paymentTotals as $currency => $value) {
            $invoicePaymentCurrencies[$currency] = [
                'currency_code' => $currency,
                'total' => $value
            ];
            $paymentSubtotal = $paymentSubtotals[$currency] ?? null;
            if ($paymentSubtotal !== null) {
                $invoicePaymentCurrencies[$currency]['subtotal'] = $paymentSubtotal;
            }
        }
        $result = [];
        foreach ($invoicePaymentCurrencies as $invoicePaymentCurrency) {
            $result[] = $invoicePaymentCurrency;
        }

        $data['invoice_payment_currency'] = $result;
        unset($data['paymentTotals'], $data['paymentSubtotals']);
    }

    private function renameFields(array &$data): void
    {
        $fieldsKeyToChange = [
            'invoice_time' => 'created_date',
            'id' => 'bitpay_id',
            'url' => 'bitpay_url',
            'order_id' => 'bitpay_order_id',
            'pos_data' => 'pos_data_json',
            'currency' => 'currency_code'
        ];

        foreach ($fieldsKeyToChange as $outdatedKey => $appStructureKey) {
            if (!array_key_exists($outdatedKey, $data)) {
                continue;
            }

            $value = $data[$outdatedKey] ?? null;
            $data[$appStructureKey] = $value;
            unset($data[$outdatedKey]);
        }
    }

    private function addDateToFields(array &$data): void
    {
        $dateFields = ['expiration_time', 'created_date'];

        foreach ($dateFields as $dateField) {
            if (!array_key_exists($dateField, $data)) {
                continue;
            }

            $value = $data[$dateField] ?? null;
            if ($value) {
                $value = DateTimeImmutableCreator::fromTimestamp($value);
            }

            $data[$dateField] = $value;
        }
    }

    private function removeUnusedFields(array &$data): void
    {
        $unusedFields = ['current_time'];

        foreach ($unusedFields as $unusedField) {
            if (array_key_exists($unusedField, $data)) {
                unset($data[$unusedField]);
            }
        }
    }

    private function mapToSpecificType(array &$data): void
    {
        $stringValues = ['exception_status'];

        foreach ($stringValues as $stringValue) {
            if (!array_key_exists($stringValue, $data)) {
                continue;
            }

            $value = $this->getStringValue($data[$stringValue]);

            $data[$stringValue] = $value;
        }
    }

    private function getStringValue(mixed $value): string
    {
        if (!is_bool($value)) {
            return (string)$value;
        }

        if ($value === true) {
            return 'true';
        }

        return 'false';
    }

    private function changeStructure(array &$data): void
    {
        $structure = [
            'amount_paid' => 'invoice_payment|amount_paid'
        ];

        foreach ($structure as $key => $itemStructure) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];

            unset($data[$key]);

            $array = $this->explodeToNestedArray('|', $itemStructure, $value);

            $data = array_merge($data, $array);
        }
    }

    private function explodeToNestedArray(string $delimeter, string $key, $value)
    {
        $keys = explode($delimeter, $key);
        while ($key = array_pop($keys)) {
            $value = [$key => $value];
        }
        return $value;
    }
}
