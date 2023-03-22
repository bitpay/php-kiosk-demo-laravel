<?php

declare(strict_types=1);

namespace App\Models\Invoice;

use App\Models\AppModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property null|int id
 * @property null|string pos_data_json
 * @property null|float price
 * @property null|string currency_code
 * @property null|string bitpay_id
 * @property null|string status
 * @property null|\Datetime created_date
 * @property null|\Datetime expiration_time
 * @property null|string bitpay_order_id
 * @property null|string facade_type
 * @property null|string bitpay_guid
 * @property null|string exception_status
 * @property null|string bitpay_url
 * @property null|string redirect_url
 * @property null|string close_url
 * @property null|int acceptance_window
 * @property null|string token
 * @property null|string merchant_name
 * @property null|string item_description
 * @property null|string bill_id
 * @property null|bool target_confirmations
 * @property null|bool low_fee_detected
 * @property null|bool auto_redirect
 * @property null|string shopper_user
 * @property null|string json_pay_pro_required
 * @property null|string bitpay_id_required
 * @property null|bool is_cancelled
 * @property null|string transaction_speed
 * @property string|null url
 * @property string|null $uuid
 * @property BelongsTo invoice_payment
 * @property BelongsTo invoice_buyer
 * @property BelongsTo invoice_refund
 * @property HasMany invoice_transactions
 * @property HasMany invoice_itemized_details
 */
class Invoice extends AppModel
{
    use HasFactory;

    protected $table ='invoice';

    protected $cast = [
        'acceptance_window' => 'boolean',
        'target_confirmations' => 'boolean',
        'low_fee_detected' => 'boolean',
        'auto_redirect' => 'boolean',
        'bitpay_id_required' => 'boolean',
        'is_cancelled' => 'boolean'
    ];

    protected $fillable = [
        'pos_data_json',
        'price',
        'currency_code',
        'bitpay_id',
        'status',
        'created_date',
        'expiration_time',
        'bitpay_order_id',
        'facade_type',
        'bitpay_guid',
        'exception_status',
        'bitpay_url',
        'redirect_url',
        'close_url',
        'acceptance_window',
        'token',
        'merchant_name',
        'item_description',
        'bill_id',
        'target_confirmations',
        'low_fee_detected',
        'auto_redirect',
        'shopper_user',
        'json_pay_pro_required',
        'bitpay_id_required',
        'is_cancelled',
        'transaction_speed',
        'uuid'
    ];

    public function invoicePayment(): BelongsTo
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    public function invoiceBuyer(): BelongsTo
    {
        return $this->belongsTo(InvoiceBuyer::class);
    }

    public function invoiceRefund(): BelongsTo
    {
        return $this->belongsTo(InvoiceRefund::class);
    }

    public function invoiceTransactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
    }

    public function invoiceItemizedDetails(): HasMany
    {
        return $this->hasMany(InvoiceItemizedDetail::class);
    }

    public function getCreatedDateAttribute($value)
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        return new \DateTimeImmutable($value);
    }

    public function getExpirationTimeAttribute($value)
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        return new \DateTimeImmutable($value);
    }

    public function getInvoicePayment(): ?InvoicePayment
    {
        return $this->getAttribute('invoicePayment');
    }

    public function getInvoiceBuyer(): ?InvoiceBuyer
    {
        return $this->getAttribute('invoiceBuyer');
    }

    public function getInvoiceRefund(): ?InvoiceRefund
    {
        return $this->getAttribute('invoiceRefund');
    }

    public function getLowFeeDetectedAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }

    public function getTargetConfirmationsAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }

    public function getAutoRedirectAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }

    public function getIsCancelledAttribute($value): ?bool
    {
        return $this->getBooleanValue($value);
    }
}
