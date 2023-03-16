<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppModel extends Model
{
    public $timestamps = false;

    protected function getDateTimeImmutable($value)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        return new \DateTimeImmutable($value);
    }

    protected function getBooleanValue($value): ?bool
    {
        if (null === $value) {
            return null;
        }

        return (bool)$value;
    }
}
