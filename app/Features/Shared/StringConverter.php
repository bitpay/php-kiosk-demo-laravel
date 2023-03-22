<?php

declare(strict_types=1);

namespace App\Features\Shared;

interface StringConverter
{
    public function toSnakeCaseArray(array $data, array $excludesKeys = []): array;
}
