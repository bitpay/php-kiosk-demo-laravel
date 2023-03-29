<?php

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

interface BitPayConfigurationFactoryInterface
{
    public function create(): BitPayConfigurationInterface;
}
