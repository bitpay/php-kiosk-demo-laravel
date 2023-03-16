<?php

declare(strict_types=1);

namespace App\Configuration;

interface BitPayConfigurationInterface
{
    /**
     * @return Design
     */
    public function getDesign(): Design;

    /**
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * @return string|null
     */
    public function getNotificationEmail(): ?string;

    public function getEnvironment(): string;

    public function getCurrencyIsoCode(): string;

    public function getFacade(): string;
}
