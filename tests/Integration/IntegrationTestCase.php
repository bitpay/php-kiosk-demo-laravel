<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['application-file' => 'application-example.yaml']);
    }

    public function tearDown(): void
    {
        config(['application-file' => null]);

        parent::tearDown();
    }
}
