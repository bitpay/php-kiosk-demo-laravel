<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

class BitPayConfiguration implements BitPayConfigurationInterface
{
    private ?string $environment;
    private ?string $facade;
    private ?Design $design;
    private ?string $token;
    private ?string $notificationEmail;

    /**
     * @param string $facade
     * @param string|null $environment
     * @param Design|null $design
     * @param string|null $token
     * @param string|null $notificationEmail
     */
    public function __construct(
        ?string $facade,
        ?string $environment,
        ?Design $design,
        ?string $token,
        ?string $notificationEmail
    ) {
        $this->environment = $environment;
        $this->facade = $facade;
        $this->design = $design;
        $this->token = $token;
        $this->notificationEmail = $notificationEmail;
    }
    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        if (!$this->environment) {
            return 'test';
        }

        return $this->environment;
    }

    public function getFacade(): string
    {
        if (!$this->facade) {
            return 'pos';
        }

        return $this->facade;
    }

    /**
     * @return Design
     */
    public function getDesign(): Design
    {
        return $this->design;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getNotificationEmail(): ?string
    {
        return $this->notificationEmail;
    }

    public function getCurrencyIsoCode(): string
    {
        foreach ($this->getDesign()->getPosData()->getFields() as $field) {
            if ($field->getType() !== 'price') {
                continue;
            }

            return $field->getCurrency();
        }

        return 'USD';
    }

    public function isSignRequest(): bool
    {
        return $this->getFacade() !== 'pos';
    }
}
