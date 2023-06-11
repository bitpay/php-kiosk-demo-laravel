<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

class Donation
{
    private array $denominations;
    private bool $enableOther;
    private string $footerText;
    private string $buttonSelectedBgColor;
    private string $buttonSelectedTextColor;

    /**
     * Donation constructor.
     * @param array $denominations
     * @param bool $enableOther
     * @param string $footerText
     * @param string $buttonSelectedBgColor
     * @param string $buttonSelectedTextColor
     */
    public function __construct(
        array $denominations,
        bool $enableOther,
        string $footerText,
        string $buttonSelectedBgColor,
        string $buttonSelectedTextColor
    ) {
        $this->denominations = $denominations;
        $this->enableOther = $enableOther;
        $this->footerText = $footerText;
        $this->buttonSelectedBgColor = $buttonSelectedBgColor;
        $this->buttonSelectedTextColor = $buttonSelectedTextColor;
    }

    /**
     * @return array
     */
    public function getDenominations(): array
    {
        return $this->denominations;
    }

    /**
     * @return bool
     */
    public function isEnableOther(): bool
    {
        return $this->enableOther;
    }

    /**
     * @return string
     */
    public function getFooterText(): string
    {
        return $this->footerText;
    }

    /**
     * @return string
     */
    public function getButtonSelectedBgColor(): string
    {
        return $this->buttonSelectedBgColor;
    }

    /**
     * @return string
     */
    public function getButtonSelectedTextColor(): string
    {
        return $this->buttonSelectedTextColor;
    }

    public function getMaxPrice(): int
    {
        return max($this->getDenominations());
    }
}
