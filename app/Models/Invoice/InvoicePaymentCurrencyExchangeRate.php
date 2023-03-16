<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;

/**
 * @property string currency_code
 * @property string rate
 */
class InvoicePaymentCurrencyExchangeRate extends AppModel
{
    protected $table = 'invoice_payment_currency_exchange_rate';

    protected $fillable = [
        'currency_code',
        'rate',
    ];
}
