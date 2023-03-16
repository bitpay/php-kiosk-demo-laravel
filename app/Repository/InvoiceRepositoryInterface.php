<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Invoice\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    public const PER_PAG = 10;

    public function findOne(int $id): ?Invoice;

    public function findPaginated(int $pageNumber, int $perPage = self::PER_PAG): LengthAwarePaginator;
}
