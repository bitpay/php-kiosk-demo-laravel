<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property BelongsTo invoice_id
 * @property int|null amount
 * @property int|null confirmations
 * @property \Datetime|null received_time
 * @property string|null txid
 */
class InvoiceTransaction extends AppModel
{
    protected $table = 'invoice_transaction';

    protected $fillable = [
        'amount',
        'confirmations',
        'received_time',
        'txid',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceTransactionExRates(): HasMany
    {
        return $this->hasMany(InvoiceTransactionExRate::class);
    }

    public function getReceivedTimeAttribute($value)
    {
        return $this->getDateTimeImmutable($value);
    }
}
