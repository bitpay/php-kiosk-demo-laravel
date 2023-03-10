<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Invoice\Invoice;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOne(int $id): ?Invoice
    {
        return Invoice::find($id);
    }
}
