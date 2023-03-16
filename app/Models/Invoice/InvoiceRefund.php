<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property BelongsTo|null invoice_refund_info_id
 * @property string|null addresses_json
 * @property string|null address_request_pending
 */
class InvoiceRefund extends AppModel
{
    protected $table = 'invoice_refund';

    protected $fillable = [
        'addresses_json',
        'address_request_pending',
    ];

    public function invoiceRefundInfo(): BelongsTo
    {
        return $this->belongsTo(InvoiceRefundInfo::class);
    }

    public function getInvoiceRefundInfo(): ?InvoiceRefundInfo
    {
        return $this->getAttribute('invoiceRefundInfo');
    }
}
