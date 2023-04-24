<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared;

class DateTimeImmutableCreator
{
    public static function fromTimestamp(?int $timestamp): ?\DateTimeImmutable
    {
        $convertedTimestamp = (int)($timestamp / 1000);
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($convertedTimestamp);

        return \DateTimeImmutable::createFromMutable($dateTime);
    }
}
