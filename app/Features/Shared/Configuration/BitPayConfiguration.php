<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

class BitPayConfiguration implements BitPayConfigurationInterface
{
    private string $environment;
    private string $facade;
    private Donation $donation;
    private Design $design;
    private Mode $mode;
    private ?string $token;
    private ?string $notificationEmail;

    /**
     * @param string $facade
     * @param string $environment
     * @param Design $design
     * @param string|null $token
     * @param string|null $notificationEmail
     */
    public function __construct(
        string $facade,
        string $environment,
        Design $design,
        Donation $donation,
        Mode $mode,
        ?string $token,
        ?string $notificationEmail
    ) {
        $this->environment = $environment;
        $this->facade = $facade;
        $this->design = $design;
        $this->donation = $donation;
        $this->mode = $mode;
        $this->token = $token;
        $this->notificationEmail = $notificationEmail;
    }
    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getFacade(): string
    {
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
     * @return Donation
     */
    public function getDonation(): Donation
    {
        return $this->donation;
    }

    /**
     * @return Mode
     */
    public function getMode(): Mode
    {
        return $this->mode;
    }

    /**
     * @param Mode $mode
     */
    public function setMode(Mode $mode)
    {
        $this->mode = $mode;
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
