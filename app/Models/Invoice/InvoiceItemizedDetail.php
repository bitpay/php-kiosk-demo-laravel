<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property BelongsTo invoice_id
 * @property string|null amount
 * @property string|null description
 * @property bool is_fee
 */
class InvoiceItemizedDetail extends AppModel
{
    protected $table = 'invoice_itemized_details';

    protected $fillable = [
        'amount',
        'description',
        'is_fee',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getIsFeeAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }
}
