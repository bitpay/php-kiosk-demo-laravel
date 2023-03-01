<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property BelongsTo invoice_transaction_id
 * @property string currency
 * @property float amount
 */
class InvoiceTransactionExRate extends AppModel
{
    protected $table = 'invoice_transaction_ex_rate';

    protected $fillable = [
        'currency',
        'amount'
    ];

    public function invoiceTransaction(): BelongsTo
    {
        return $this->belongsTo(InvoiceTransaction::class);
    }
}
