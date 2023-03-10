<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Invoice\Invoice;

interface InvoiceRepositoryInterface
{
    public function findOne(int $id): ?Invoice;
}
