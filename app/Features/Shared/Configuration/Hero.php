<?php

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

class Hero
{
    private string $bgColor;
    private string $title;
    private string $body;

    /**
     * Hero constructor.
     * @param string $bgColor
     * @param string $title
     * @param string $body
     */
    public function __construct(string $bgColor, string $title, string $body)
    {
        $this->bgColor = $bgColor;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBgColor(): string
    {
        return $this->bgColor;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
