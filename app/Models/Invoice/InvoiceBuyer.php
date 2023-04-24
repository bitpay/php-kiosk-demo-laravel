<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null name,
 * @property string|null address1,
 * @property string|null address2,
 * @property string|null city,
 * @property string|null region,
 * @property string|null postal_code,
 * @property string|null country,
 * @property string|null email,
 * @property string|null phone,
 * @property string|null notify,
 * @property string|null buyer_provided_email,
 * @property BelongsTo invoice_buyer_provided_info_id,
 *
 */
class InvoiceBuyer extends AppModel
{
    protected $table = 'invoice_buyer';

    protected $fillable = [
        'name',
        'address1',
        'address2',
        'city',
        'region',
        'postal_code',
        'country',
        'email',
        'phone',
        'notify',
        'buyer_provided_email',
    ];

    public function invoiceBuyerProvidedInfo(): BelongsTo
    {
        return $this->belongsTo(InvoiceBuyerProvidedInfo::class);
    }

    public function getInvoiceBuyerProvidedInfo(): ?InvoiceBuyerProvidedInfo
    {
        return $this->getAttribute('invoiceBuyerProvidedInfo');
    }
}
