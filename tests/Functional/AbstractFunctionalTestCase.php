<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AbstractFunctionalTestCase extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['application-file' => 'application-functional.yaml']);
    }

    public function tearDown(): void
    {
        config(['application-file' => null]);

        parent::tearDown();
    }
}
