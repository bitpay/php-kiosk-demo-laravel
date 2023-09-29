<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Repository;

use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOne(int $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function findOneByUuid(string $uuid): ?Invoice
    {
        return Invoice::where('uuid', $uuid)->first();
    }

    public function findPaginated(int $pageNumber, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Invoice::paginate($perPage, ['*'], 'page', $pageNumber);
    }
}
