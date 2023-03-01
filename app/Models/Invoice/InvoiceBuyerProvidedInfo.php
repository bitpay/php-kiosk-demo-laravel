<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;

/**
 * @property string|null name,
 * @property string|null phone_number
 * @property string|null selected_wallet
 * @property string|null email_address
 * @property string|null selected_transaction_currency
 * @property string|null sms
 * @property string|null sms_verified
 */
class InvoiceBuyerProvidedInfo extends AppModel
{
    protected $table = 'invoice_buyer_provided_info';

    protected $fillable = [
        'name',
        'phone_number',
        'selected_wallet',
        'email_address',
        'selected_transaction_currency',
        'sms',
        'sms_verified',
    ];
}
