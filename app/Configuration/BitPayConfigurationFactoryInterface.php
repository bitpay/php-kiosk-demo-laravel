<?php

declare(strict_types=1);

namespace App\Configuration;

interface BitPayConfigurationFactoryInterface
{
    public function create(): BitPayConfigurationInterface;
}
