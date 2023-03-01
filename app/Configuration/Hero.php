<?php

declare(strict_types=1);

namespace App\Configuration;

class Hero
{
    private string $bgColor;

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
     * @param string $bgColor
     */
    public function setBgColor(string $bgColor): void
    {
        $this->bgColor = $bgColor;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }
    private string $title;
    private string $body;
}
