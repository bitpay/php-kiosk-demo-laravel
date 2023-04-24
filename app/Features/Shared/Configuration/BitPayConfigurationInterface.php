<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

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

    public function isSignRequest(): bool;
}
