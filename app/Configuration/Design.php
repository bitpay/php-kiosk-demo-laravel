<?php

declare(strict_types=1);

namespace App\Configuration;

class Design
{
    private Hero $hero;
    private String $logo;
    private PosData $posData;

    /**
     * Design constructor.
     * @param Hero $hero
     * @param String $logo
     * @param PosData $posData
     */
    public function __construct(Hero $hero, string $logo, PosData $posData)
    {
        $this->hero = $hero;
        $this->logo = $logo;
        $this->posData = $posData;
    }

    /**
     * @return Hero
     */
    public function getHero(): Hero
    {
        return $this->hero;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * @return PosData
     */
    public function getPosData(): PosData
    {
        return $this->posData;
    }


}
