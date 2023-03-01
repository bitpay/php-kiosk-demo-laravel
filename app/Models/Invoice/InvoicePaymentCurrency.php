<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property BelongsTo invoice_payment_id
 * @property BelongsTo supported_transaction_currency_id
 * @property BelongsTo miner_fee_id
 * @property string currency_code
 * @property string total
 * @property string subtotal
 * @property string display_total
 * @property string display_subtotal
 */
class InvoicePaymentCurrency extends AppModel
{
    protected $table = 'invoice_payment_currency';

    protected $fillable = [
        'currency_code',
        'total',
        'subtotal',
        'display_total',
        'display_subtotal',
        'non_pay_pro_payment_received',
    ];

    public function invoicePayment(): BelongsTo
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    public function minerFee(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentCurrencyMinerFee::class);
    }

    public function supportedTransactionCurrency(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentCurrencySupportedTransactionCurrency::class);
    }

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(InvoicePaymentCurrencyExchangeRate::class);
    }

    public function currencyCodes(): HasMany
    {
        return $this->hasMany(InvoicePaymentCurrencyCode::class);
    }

    public function getMinerFee(): ?InvoicePaymentCurrencyMinerFee
    {
        return $this->getAttribute('minerFee');
    }

    public function getSupportedTransactionCurrency(): ?InvoicePaymentCurrencySupportedTransactionCurrency
    {
        return $this->getAttribute('supportedTransactionCurrency');
    }

    public function getExchangeRates(): Collection
    {
        return $this->getAttribute('exchangeRates');
    }
}
