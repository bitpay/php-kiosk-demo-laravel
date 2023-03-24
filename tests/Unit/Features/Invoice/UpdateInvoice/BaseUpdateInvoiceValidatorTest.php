<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Invoice\UpdateInvoice;

use App\Shared\Exceptions\ValidationFailed;
use App\Features\Invoice\UpdateInvoice\BaseUpdateInvoiceValidator;
use Tests\AbstractUnitTest;

class BaseUpdateInvoiceValidatorTest extends AbstractUnitTest
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
