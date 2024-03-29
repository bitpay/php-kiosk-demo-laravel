<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use App\Features\Invoice\UpdateInvoice\BaseUpdateInvoiceValidator;
use Tests\Unit\AbstractUnitTestCase;

class BaseUpdateInvoiceValidatorTestCase extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_throws_exception_for_missing_data(): void
    {
        $this->expectException(ValidationFailed::class);

        $testedClass = new BaseUpdateInvoiceValidator();
        $testedClass->execute(null, null);
    }
}
