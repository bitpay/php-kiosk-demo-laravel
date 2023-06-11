<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Unit\Configuration;

use App\Features\Shared\Configuration\Hero;
use Tests\Unit\AbstractUnitTestCase;

class HeroTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function it_should_return_bg_color(): void
    {
        $bgColor = '#123';
        $hero = new Hero($bgColor, 'someTitle', 'someBody');

        self::assertEquals($bgColor, $hero->getBgColor());
    }

    /**
     * @test
     */
    public function it_should_return_title(): void
    {
        $title = 'someTitle';
        $hero = new Hero('#123', $title, 'someBody');

        self::assertEquals($title, $hero->getTitle());
    }

    /**
     * @test
     */
    public function it_should_return_body(): void
    {
        $body = 'someBody';
        $hero = new Hero('#123', 'someTitle', $body);

        self::assertEquals($body, $hero->getBody());
    }
}
