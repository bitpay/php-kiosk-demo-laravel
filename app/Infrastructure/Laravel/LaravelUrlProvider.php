<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Infrastructure\Laravel;

use App\Features\Shared\UrlProvider;
use Illuminate\Support\Facades\URL;

class LaravelUrlProvider implements UrlProvider
{
    public function applicationUrl(): string
    {
        $url = config('APP_URL');
        if ($url) {
            return $url . '/';
        }

        return Url::to('/');
    }
}
