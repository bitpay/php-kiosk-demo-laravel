<?php

declare(strict_types=1);

namespace Tests\Unit\Configuration;

use App\Features\Shared\Configuration\Design;
use App\Features\Shared\Configuration\Hero;
use App\Features\Shared\Configuration\PosData;
use Tests\AbstractUnitTest;

class DesignTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function it_should_provide_hero(): void
    {
        $hero = $this->createMock(Hero::class);
        $logo = 'someLogo.png';
        $posData = $this->createMock(PosData::class);

        $testedClass = new Design($hero, $logo, $posData);
        self::assertEquals($hero, $testedClass->getHero());
    }

    /**
     * @test
     */
    public function it_should_provide_logo(): void
    {
        $hero = $this->createMock(Hero::class);
        $logo = 'someLogo.png';
        $posData = $this->createMock(PosData::class);

        $testedClass = new Design($hero, $logo, $posData);
        self::assertEquals($logo, $testedClass->getLogo());
    }

    /**
     * @test
     */
    public function it_should_provide_pos_data(): void
    {
        $hero = $this->createMock(Hero::class);
        $logo = 'someLogo.png';
        $posData = $this->createMock(PosData::class);

        $testedClass = new Design($hero, $logo, $posData);
        self::assertEquals($posData, $testedClass->getPosData());
    }
}
