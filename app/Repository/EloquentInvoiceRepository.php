<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Invoice\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOne(int $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function findPaginated(int $pageNumber, int $perPage = self::PER_PAG): LengthAwarePaginator
    {
        return Invoice::paginate($perPage, ['*'], 'page', $pageNumber);
    }
}
