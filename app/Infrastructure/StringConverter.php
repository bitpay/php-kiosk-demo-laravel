<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Illuminate\Support\Str;

class StringConverter implements \App\Features\Shared\StringConverter
{
    public function toSnakeCaseArray(array $data, array $excludesKeys = []): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (\in_array($key, $excludesKeys, true)) {
                $result[$key] = $value;
                continue;
            }

            $result[Str::snake($key)] = $value;
        }

        return $result;
    }
}
