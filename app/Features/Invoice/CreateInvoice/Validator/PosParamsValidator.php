<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\CreateInvoice\Validator;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Shared\Exceptions\ValidationFailed;

class PosParamsValidator implements CreateInvoiceValidator
{
    private BitPayConfigurationInterface $bitPayConfiguration;

    public function __construct(BitPayConfigurationInterface $bitPayConfiguration)
    {
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function execute(array $params): array
    {
        $requiredParametersName = [];

        $bitPayFields = $this->bitPayConfiguration->getDesign()->getPosData()->getFields();
        foreach ($bitPayFields as $field) {
            if ($field->isRequired() === true) {
                $requiredParametersName[] = $field->getName();
            }
        }

        foreach ($requiredParametersName as $requiredParameterName) {
            $value = $params[$requiredParameterName] ?? null;
            if (!$value) {
                throw new ValidationFailed('Missing required field ' . $requiredParameterName);
            }
        }

        $validatedParams = [];
        foreach ($bitPayFields as $bitPayField) {
            $parameterName = $bitPayField->getName();
            if (array_key_exists($parameterName, $params)) {
                $validatedParams[$parameterName] = $params[$parameterName];
            }
        }

        if (!array_key_exists('price', $validatedParams)) {
            throw new ValidationFailed('Missing price');
        }

        $validatedParams['price'] = number_format((float)$validatedParams['price'], 2);

        return $validatedParams;
    }
}
