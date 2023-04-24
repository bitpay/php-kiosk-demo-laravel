<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;

/**
 * @property int|null satoshis_per_byte
 * @property float|null total_fee
 * @property float|null fiat_amount
 */
class InvoicePaymentCurrencyMinerFee extends AppModel
{
    protected $table = 'invoice_payment_currency_miner_fee';

    protected $fillable = [
        'satoshis_per_byte',
        'total_fee',
        'fiat_amount',
    ];
}
