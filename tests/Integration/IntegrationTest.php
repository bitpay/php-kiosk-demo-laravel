<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['test.type' => 'integration']);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        config(['test.type' => null]);
    }
}
