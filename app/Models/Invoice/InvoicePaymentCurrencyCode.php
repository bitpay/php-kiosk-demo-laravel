<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null code
 * @property string|null code_url
 */
class InvoicePaymentCurrencyCode extends AppModel
{
    protected $table = 'invoice_payment_currency_code';

    protected $fillable = [
        'code',
        'code_url',
    ];

    public function invoicePaymentCurrency(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentCurrency::class);
    }
}
