<?php

declare(strict_types=1);

namespace App\Features\Shared;

interface UrlProvider
{
    public function applicationUrl(): string;
}
