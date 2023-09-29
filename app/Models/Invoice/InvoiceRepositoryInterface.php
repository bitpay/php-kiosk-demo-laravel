<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\Invoice\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface
{
    public const PER_PAGE = 10;

    public function findOne(int $id): ?Invoice;

    public function findPaginated(int $pageNumber, int $perPage = self::PER_PAGE): LengthAwarePaginator;

    public function findOneByUuid(string $uuid): ?Invoice;
}
