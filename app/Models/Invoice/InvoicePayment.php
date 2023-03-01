<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property float|null amount_paid
 * @property float|null display_amount_paid
 * @property float|null underpaid_amount
 * @property float|null overpaid_amount
 * @property bool|null non_pay_pro_payment_received
 * @property string|null transaction_currency
 * @property string|null universal_codes_payment_string
 * @property string|null universal_codes_verification_link
 */
class InvoicePayment extends AppModel
{
    protected $table = 'invoice_payment';

    protected $fillable = [
        'amount_paid',
        'display_amount_paid',
        'underpaid_amount',
        'overpaid_amount',
        'non_pay_pro_payment_received',
        'transaction_currency',
        'universal_codes_payment_string',
        'universal_codes_verification_link',
    ];

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function paymentCurrencies(): HasMany
    {
        return $this->hasMany(InvoicePaymentCurrency::class);
    }

    public function getPaymentCurrencies(): Collection
    {
        return $this->getAttribute('paymentCurrencies');
    }

    public function getNonPayProPaymentReceivedAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }
}
