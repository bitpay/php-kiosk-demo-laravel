<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\CreateInvoice\Validator;

use App\Shared\Exceptions\ValidationFailed;

interface CreateInvoiceValidator
{
    /**
     * @param array $params
     * @return array $validatedParams
     * @throws ValidationFailed
     */
    public function execute(array $params): array;
}
