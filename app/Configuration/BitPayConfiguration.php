<?php

declare(strict_types=1);

namespace App\Configuration;

class BitPayConfiguration implements BitPayConfigurationInterface
{
    public string $environment;
    public string $facade;
    public ?Design $design;
    public ?string $token;
    public ?string $notificationEmail;

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


}
