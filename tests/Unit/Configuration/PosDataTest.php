<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Configuration;

use App\Features\Shared\Configuration\Field;
use App\Features\Shared\Configuration\PosData;
use Tests\Unit\AbstractUnitTestCase;

class PosDataTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_return_fields(): void
    {
        $field = $this->createMock(Field::class);
        $fields = [$field];

        $posData = new PosData();
        $posData->setFields($fields);

        self::assertEquals($fields, $posData->getFields());
    }
}
