<?php

declare(strict_types=1);

namespace App\Features\Shared;

use App\Features\Shared\Configuration\BitPayConfigurationFactoryInterface;
use BitPaySDK\Client;
use BitPaySDK\PosClient;

class BitPayClientFactory
{
    private BitPayConfigurationFactoryInterface $bitPayConfigurationFactory;

    public function __construct(BitPayConfigurationFactoryInterface $bitPayConfigurationFactory)
    {
        $this->bitPayConfigurationFactory = $bitPayConfigurationFactory;
    }

    /**
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    public function create(): Client
    {
        $bitPayConfiguration = $this->bitPayConfigurationFactory->create();

        return new PosClient($bitPayConfiguration->getToken(), $bitPayConfiguration->getEnvironment());
    }
}
