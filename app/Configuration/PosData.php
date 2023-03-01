<?php

declare(strict_types=1);

namespace App\Configuration;

class PosData
{
    /** @var Field[] */
    private array $fields = [];

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }
}
