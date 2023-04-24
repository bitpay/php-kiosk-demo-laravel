<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null currency_code
 * @property string|null support_request
 */
class InvoiceRefundInfo extends AppModel
{
    protected $table = 'invoice_refund_info';

    protected $fillable = [
        'currency_code',
        'support_request',
    ];

    public function invoiceRefundInfoAmounts(): HasMany
    {
        return $this->hasMany(InvoiceRefundInfoAmount::class);
    }
}
