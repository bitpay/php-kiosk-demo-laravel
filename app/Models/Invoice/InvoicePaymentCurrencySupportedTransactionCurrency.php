<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;

/**
 * @property bool enabled
 * @property string|null reason
 */
class InvoicePaymentCurrencySupportedTransactionCurrency extends AppModel
{
    protected $table = 'invoice_payment_currency_supported_transaction_currency';

    protected $fillable = [
        'enabled',
        'reason',
    ];

    public function getEnabledAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }
}
