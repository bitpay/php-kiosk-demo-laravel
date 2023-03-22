<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel;

use App\Features\Shared\UrlProvider;
use Illuminate\Support\Facades\URL;

class LaravelUrlProvider implements UrlProvider
{
    public function applicationUrl(): string
    {
        return Url::to('/');
    }
}
