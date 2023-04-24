<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

enum UpdateInvoiceEventType
{
    case SUCCESS;
    case ERROR;
}
