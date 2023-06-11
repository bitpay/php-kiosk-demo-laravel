<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\CreateInvoice\Validator;

use App\Shared\Exceptions\ValidationFailed;

class DonationParamsValidator implements CreateInvoiceValidator
{
    private CreateInvoiceValidator $validator;

    public function __construct(PosParamsValidator $validator)
    {
        $this->validator = $validator;
    }

    public function execute(array $params): array
    {
        $buyerParams = [];
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'buyer')) {
                $buyerParams[$key] = $value;
            }
        }

        $this->validateEmail($buyerParams['buyerEmail'] ?? null);
        $this->validatePostalCode($buyerParams['buyerPostalCode'] ?? null);
        $this->validateRegion($buyerParams['buyerRegion'] ?? null);
        $this->validateNonNullValues($buyerParams);

        return array_merge($this->validator->execute($params), $buyerParams);
    }

    private function validatePostalCode(?string $buyerPostalCode): void
    {
        if (!$buyerPostalCode || !preg_match($this->usZipCodeRegex(), $buyerPostalCode)) {
            throw new ValidationFailed("Wrong Postal Code");
        }
    }

    private function validateEmail(?string $buyerEmail): void
    {
        if (!$buyerEmail || !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationFailed('Wrong email');
        }
    }

    private function validateRegion(?string $region): void
    {
        $availableRegions = [
            'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN',
            'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY',
            'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
        ];

        if (!$region || !in_array($region, $availableRegions, true)) {
            throw new ValidationFailed('Wrong region');
        }
    }

    /**
     * @param array $params
     */
    private function validateNonNullValues(array $params): void
    {
        $nonNullKeys = ['buyerName', 'buyerAddress1', 'buyerLocality', 'buyerPostalCode', 'buyerPhone'];

        foreach ($nonNullKeys as $key) {
            if (!isset($params[$key])) {
                throw new ValidationFailed('You should fill all required fields');
            }
        }
    }

    /**
     * @return string
     */
    private function usZipCodeRegex(): string
    {
        return '/^\d{5}(?:-\d{4})?$/'; # 5 numbers or 5 numbers + hyphen + 4 numbers
    }
}
