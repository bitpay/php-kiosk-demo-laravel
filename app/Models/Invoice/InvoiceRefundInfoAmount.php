<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property BelongsTo invoice_refund_info_id
 * @property string currency_code
 * @property float amount
 */
class InvoiceRefundInfoAmount extends AppModel
{
    protected $table = 'invoice_refund_info_amount';

    protected $fillable = [
        'currency_code',
        'amount',
    ];

    public function invoiceRefundInfo(): BelongsTo
    {
        return $this->belongsTo(InvoiceRefundInfo::class);
    }
}
