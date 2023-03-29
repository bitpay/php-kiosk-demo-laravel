<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

enum UpdateInvoiceEventType
{
    case SUCCESS;
    case ERROR;
}
