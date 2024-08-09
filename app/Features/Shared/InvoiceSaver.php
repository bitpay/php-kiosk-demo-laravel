<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared;

use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceBuyer;
use App\Models\Invoice\InvoiceBuyerProvidedInfo;
use App\Models\Invoice\InvoiceItemizedDetail;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;
use App\Models\Invoice\InvoicePaymentCurrencyCode;
use App\Models\Invoice\InvoicePaymentCurrencyExchangeRate;
use App\Models\Invoice\InvoicePaymentCurrencyMinerFee;
use App\Models\Invoice\InvoicePaymentCurrencySupportedTransactionCurrency;
use App\Models\Invoice\InvoiceRefund;
use App\Models\Invoice\InvoiceRefundInfo;
use App\Models\Invoice\InvoiceRefundInfoAmount;
use App\Models\Invoice\InvoiceTransaction;
use App\Models\Invoice\InvoiceTransactionExRate;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;
use BitPaySDK\Model\Invoice\MinerFeesItem;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrencies;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrency;

class InvoiceSaver
{
    /**
     * @throws \JsonException
     */
    public function fromSdkModel(BitPayInvoice $bitpayInvoice, string $uuid): Invoice
    {
        $invoice = new Invoice();

        $payment = $this->getPayment($bitpayInvoice);
        $buyer = $this->getBuyer($bitpayInvoice);
        $refund = $this->getRefund($bitpayInvoice);

        $createdDate = $bitpayInvoice->getInvoiceTime() ?
            DateTimeImmutableCreator::fromTimestamp((int)$bitpayInvoice->getInvoiceTime()) : null;
        $expirationTime = $bitpayInvoice->getExpirationTime() ?
            DateTimeImmutableCreator::fromTimestamp((int)$bitpayInvoice->getExpirationTime()) : null;

        $invoice->fill([
            'pos_data_json' => $bitpayInvoice->getPosData(),
            'price' => $bitpayInvoice->getPrice(),
            'currency_code' => $bitpayInvoice->getCurrency(),
            'bitpay_id' => $bitpayInvoice->getId(),
            'status' => $bitpayInvoice->getStatus(),
            'created_date' => $createdDate,
            'expiration_time' => $expirationTime,
            'bitpay_order_id' => $bitpayInvoice->getOrderId(),
            'facade_type' => 'pos/invoice',
            'bitpay_guid' => $bitpayInvoice->getGuid(),
            'exception_status' => $bitpayInvoice->getExceptionStatus(),
            'bitpay_url' => $bitpayInvoice->getUrl(),
            'redirect_url' => $bitpayInvoice->getRedirectURL(),
            'close_url' => $bitpayInvoice->getCloseURL(),
            'acceptance_window' => $bitpayInvoice->getAcceptanceWindow(),
            'token' => $bitpayInvoice->getToken(),
            'merchant_name' => $bitpayInvoice->getMerchantName(),
            'item_description' => $bitpayInvoice->getItemDesc(),
            'bill_id' => $bitpayInvoice->getBillId(),
            'target_confirmations' => $bitpayInvoice->getTargetConfirmations(),
            'low_fee_detected' => $bitpayInvoice->getLowFeeDetected(),
            'auto_redirect' => $bitpayInvoice->getAutoRedirect(),
            'shopper_user' => $bitpayInvoice->getShopper() ? $bitpayInvoice->getShopper()->getUser() : null,
            'json_pay_pro_required' => $bitpayInvoice->getJsonPayProRequired(),
            'bitpay_id_required' => $bitpayInvoice->getBitpayIdRequired(),
            'is_cancelled' => $bitpayInvoice->getIsCancelled(),
            'transaction_speed' => $bitpayInvoice->getTransactionSpeed(),
            'url' => $bitpayInvoice->getUrl(),
            'uuid' => $uuid
        ]);

        $invoice->invoicePayment()->associate($payment);
        $invoice->invoiceBuyer()->associate($buyer);
        $invoice->invoiceRefund()->associate($refund);

        $invoice->save();

        $this->saveItemizedDetails($bitpayInvoice, $invoice);
        $this->saveTransactions($bitpayInvoice, $invoice);

        return $invoice;
    }

    /**
     * @param BitPayInvoice $bitpayInvoice
     * @return InvoiceRefund
     * @throws \JsonException
     */
    private function getRefund(BitPayInvoice $bitpayInvoice): InvoiceRefund
    {
        $invoiceRefund = new InvoiceRefund([
            'addresses_json' => $bitpayInvoice->getRefundAddresses()
                ? json_encode($bitpayInvoice->getRefundAddresses(), JSON_THROW_ON_ERROR) : null,
            'address_request_pending' => $bitpayInvoice->getRefundAddressRequestPending()
        ]);

        $bitpayRefundInfo = $bitpayInvoice->getRefundInfo();
        if (!$bitpayRefundInfo || $bitpayRefundInfo->getCurrency() === null) {
            $invoiceRefund->save();

            return $invoiceRefund;
        }

        $refundInfo = new InvoiceRefundInfo([
            'currency_code' => $bitpayRefundInfo->getCurrency(),
            'support_request' => $bitpayRefundInfo->getSupportRequest(),
        ]);
        $refundInfo->save();

        $invoiceRefund->invoiceRefundInfo()->associate($refundInfo);
        $invoiceRefund->save();

        if ($bitpayRefundInfo->getAmounts()) {
            $refundInfoAmounts = [];
            foreach ($bitpayRefundInfo->getAmounts() as $currencyCode => $amount) {
                $refundInfoAmounts[] = new InvoiceRefundInfoAmount([
                    'currency_code' => $currencyCode,
                    'amount' => $amount,
                ]);
            }
            $refundInfo->invoiceRefundInfoAmounts()->saveMany($refundInfoAmounts);
        }

        return $invoiceRefund;
    }

    /**
     * @param BitPayInvoice $bitpayInvoice
     * @return InvoiceBuyer
     */
    private function getBuyer(BitPayInvoice $bitpayInvoice): ?InvoiceBuyer
    {
        $bitpayBuyer = $bitpayInvoice->getBuyer();
        if (!$bitpayBuyer) {
            return null;
        }

        $buyerProvidedInfo = $this->getBuyerProvidedInfo($bitpayInvoice);

        $buyer = new InvoiceBuyer([
            'name' => $bitpayBuyer->getName(),
            'address1' => $bitpayBuyer->getAddress1(),
            'address2' => $bitpayBuyer->getAddress2(),
            'city' => $bitpayBuyer->getLocality(),
            'region' => $bitpayBuyer->getRegion(),
            'postal_code' => $bitpayBuyer->getPostalCode(),
            'country' => $bitpayBuyer->getCountry(),
            'email' => $bitpayBuyer->getEmail(),
            'phone' => $bitpayBuyer->getPhone(),
            'notify' => $bitpayBuyer->getNotify(),
            'buyer_provided_email' => $bitpayInvoice->getBuyerProvidedEmail(),
        ]);
        $buyer->invoiceBuyerProvidedInfo()->associate($buyerProvidedInfo);
        $buyer->save();

        return $buyer;
    }

    private function saveItemizedDetails(BitPayInvoice $bitpayInvoice, Invoice $invoice): void
    {
        $bitPayItemizedDetails = $bitpayInvoice->getItemizedDetails();
        if (!$bitPayItemizedDetails) {
            return;
        }

        $result = [];
        /** @var array $itemizedDetail */
        foreach ($bitPayItemizedDetails as $itemizedDetail) {
            if (empty($itemizedDetail)) {
                continue;
            }

            $invoiceItemizedDetail = new InvoiceItemizedDetail([
                'amount' => $itemizedDetail->getAmount(),
                'description' => $itemizedDetail->getDescription(),
                'is_fee' => $itemizedDetail->getIsFee(),
            ]);
            $result[] = $invoiceItemizedDetail;
        }

        if (!$result) {
            return;
        }

        $invoice->invoiceItemizedDetails()->saveMany($result);
    }

    /**
     * @param BitPayInvoice $bitpayInvoice
     * @return InvoiceBuyerProvidedInfo|null
     */
    private function getBuyerProvidedInfo(BitPayInvoice $bitpayInvoice): ?InvoiceBuyerProvidedInfo
    {
        $bitpayBuyerProvidedInfo = $bitpayInvoice->getBuyerProvidedInfo();
        if (!$bitpayBuyerProvidedInfo) {
            return null;
        }

        $invoiceBuyerProvidedInfo = new InvoiceBuyerProvidedInfo([
            'name' => $bitpayBuyerProvidedInfo->getName(),
            'phone_number' => $bitpayBuyerProvidedInfo->getPhoneNumber(),
            'selected_wallet' => $bitpayBuyerProvidedInfo->getSelectedWallet(),
            'email_address' => $bitpayBuyerProvidedInfo->getEmailAddress(),
            'selected_transaction_currency' => $bitpayBuyerProvidedInfo->getSelectedTransactionCurrency(),
            'sms' => $bitpayBuyerProvidedInfo->getSms(),
            'sms_verified' => $bitpayBuyerProvidedInfo->getSmsVerified(),
        ]);
        $invoiceBuyerProvidedInfo->save();

        return $invoiceBuyerProvidedInfo;
    }

    /**
     * @param BitPayInvoice $bitpayInvoice
     * @return InvoicePayment
     */
    private function getPayment(BitPayInvoice $bitpayInvoice): InvoicePayment
    {
        $universalCodes = $bitpayInvoice->getUniversalCodes();

        $invoicePayment = new InvoicePayment([
            'amount_paid' => $bitpayInvoice->getAmountPaid(),
            'display_amount_paid' => $bitpayInvoice->getDisplayAmountPaid(),
            'underpaid_amount' => $bitpayInvoice->getUnderpaidAmount(),
            'overpaid_amount' => $bitpayInvoice->getOverpaidAmount(),
            'non_pay_pro_payment_received' => $bitpayInvoice->getNonPayProPaymentReceived(),
            'universal_codes_payment_string' => $universalCodes ? $universalCodes->getPaymentString() : null,
            'universal_codes_verification_link' => $universalCodes ? $universalCodes->getVerificationLink() : null,
            'transaction_currency' => $bitpayInvoice->getTransactionCurrency(),
        ]);
        $invoicePayment->save();

        $paymentTotals = $bitpayInvoice->getPaymentTotals();
        if (!$paymentTotals) {
            return $invoicePayment;
        }

        $invoicePaymentCurrencies = [];
        foreach ($paymentTotals as $currency => $amount) {
            $invoicePaymentCurrency = $this->getInvoicePaymentCurrency(
                $bitpayInvoice,
                $invoicePayment,
                $currency,
                $amount
            );
            $invoicePaymentCurrencies[] = $invoicePaymentCurrency;
        }
        $invoicePayment->paymentCurrencies()->saveMany($invoicePaymentCurrencies);

        return $invoicePayment;
    }

    private function getInvoicePaymentCurrency(
        BitPayInvoice $bitpayInvoice,
        InvoicePayment $invoicePayment,
        string $currency,
        float $amount,
    ): InvoicePaymentCurrency {
        $invoicePaymentCurrency = new InvoicePaymentCurrency([
            'currency_code' => $currency,
            'total' => $amount,
            'subtotal' => $bitpayInvoice->getPaymentSubtotals()[$currency] ?? null,
            'display_total' => $bitpayInvoice->getPaymentDisplayTotals()[$currency] ?? null,
            'display_subtotal' => $bitpayInvoice->getPaymentDisplaySubTotals()[$currency] ?? null,
        ]);
        $invoicePaymentCurrency->invoicePayment()->associate($invoicePayment);

        $minerFee = $this->getMinerFee($bitpayInvoice, $currency);
        $invoicePaymentCurrency->minerFee()->associate($minerFee);

        $supportedTransactionCurrency = $this->getSupportedTransactionCurrency($bitpayInvoice, $currency);
        if ($supportedTransactionCurrency) {
            $invoicePaymentCurrency->supportedTransactionCurrency()->associate($supportedTransactionCurrency);
        }

        $invoicePaymentCurrency->save();

        $bitpayPaymentCodes = $bitpayInvoice->getPaymentCodes()[$currency] ?? null;
        if ($bitpayPaymentCodes) {
            $invoicePaymentCurrencyCodes = [];
            foreach ($bitpayPaymentCodes as $code => $value) {
                $invoicePaymentCurrencyCode = new InvoicePaymentCurrencyCode([
                    'code' => $code,
                    'code_url' => $value,
                ]);
                $invoicePaymentCurrencyCode->belongsTo($invoicePaymentCurrency);
                $invoicePaymentCurrencyCodes[] = $invoicePaymentCurrencyCode;
            }

            $invoicePaymentCurrency->currencyCodes()->saveMany($invoicePaymentCurrencyCodes);
        }

        $exchangeRates = $this->getExchangeRates($bitpayInvoice, $invoicePaymentCurrency, $currency);
        if ($exchangeRates) {
            $invoicePaymentCurrency->exchangeRates()->saveMany($exchangeRates);
        }

        return $invoicePaymentCurrency;
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    private function getSupportedTransactionCurrency(BitPayInvoice $bitpayInvoice, string $currency): ?InvoicePaymentCurrencySupportedTransactionCurrency
    {
        /** @var SupportedTransactionCurrencies|null $bitpaySupportedTransactionCurrency */
        $bitpaySupportedTransactionCurrencies = $bitpayInvoice->getSupportedTransactionCurrencies();
        if (!$bitpaySupportedTransactionCurrencies) {
            return $this->getDefaultSupportedTransactionCurrency();
        }

        $methodName = 'get' . strtoupper($currency);
        if (!method_exists($bitpaySupportedTransactionCurrencies, $methodName)) {
            return $this->getDefaultSupportedTransactionCurrency();
        }

        /** @var SupportedTransactionCurrency|null $bitpaySupportedTransactionCurrency */
        $bitpaySupportedTransactionCurrency = $bitpaySupportedTransactionCurrencies->{$methodName}();

        if (!$bitpaySupportedTransactionCurrency) {
            return $this->getDefaultSupportedTransactionCurrency();
        }

        $supportedTransactionCurrency = new InvoicePaymentCurrencySupportedTransactionCurrency([
            'enabled' => $bitpaySupportedTransactionCurrency->getEnabled(),
            'reason' => $bitpaySupportedTransactionCurrency->getReason(),
        ]);

        $supportedTransactionCurrency->save();
        return $supportedTransactionCurrency;
    }

    private function getMinerFee(
        BitPayInvoice $bitpayInvoice,
        string $currency
    ): InvoicePaymentCurrencyMinerFee {
        $bitpayMinerFees = $bitpayInvoice->getMinerFees();
        if (!$bitpayMinerFees) {
            return $this->getDefaultMinerFee();
        }

        $methodName = 'get' . strtoupper($currency);
        if (!method_exists($bitpayMinerFees, $methodName)) {
            return $this->getDefaultMinerFee();
        }

        /** @var MinerFeesItem|null $bitpayMinerFee */
        $bitpayMinerFee = $bitpayMinerFees->{$methodName}();
        if (!$bitpayMinerFee) {
            return $this->getDefaultMinerFee();
        }

        $minerFee = new InvoicePaymentCurrencyMinerFee([
            'satoshis_per_byte' => $bitpayMinerFee->getSatoshisPerByte(),
            'total_fee' => $bitpayMinerFee->getTotalFee(),
            'fiat_amount' => $bitpayMinerFee->getFiatAmount()
        ]);
        $minerFee->save();

        return $minerFee;
    }

    /**
     * @return InvoicePaymentCurrencyMinerFee
     */
    private function getDefaultMinerFee(): InvoicePaymentCurrencyMinerFee
    {
        $minerFee = new InvoicePaymentCurrencyMinerFee();
        $minerFee->save();

        return $minerFee;
    }

    /**
     * @param BitPayInvoice $bitpayInvoice
     * @param string $currency
     * @param InvoicePaymentCurrency $invoicePaymentCurrency
     * @return array
     */
    private function getExchangeRates(
        BitPayInvoice $bitpayInvoice,
        InvoicePaymentCurrency $invoicePaymentCurrency,
        string $currency
    ): array {
        $bitpayExchangeRates = $bitpayInvoice->getExchangeRates()[$currency] ?? null;
        if (!$bitpayExchangeRates) {
            return [];
        }

        $exchangeRates = [];
        foreach ($bitpayExchangeRates as $exchangeRateCurrency => $value) {
            $exchangeRate = new InvoicePaymentCurrencyExchangeRate([
                'currency_code' => $exchangeRateCurrency,
                'rate' => $value
            ]);
            $exchangeRate->belongsTo($invoicePaymentCurrency);
            $exchangeRates[] = $exchangeRate;
        }

        return $exchangeRates;
    }

    /**
     * @return InvoicePaymentCurrencySupportedTransactionCurrency
     */
    private function getDefaultSupportedTransactionCurrency(): InvoicePaymentCurrencySupportedTransactionCurrency
    {
        $supportedTransactionCurrency = new InvoicePaymentCurrencySupportedTransactionCurrency([
            'enabled' => false,
            'reason' => null,
        ]);
        $supportedTransactionCurrency->save();

        return $supportedTransactionCurrency;
    }

    private function saveTransactions(BitPayInvoice $bitpayInvoice, Invoice $invoice): void
    {
        $bitpayTransactions = $bitpayInvoice->getTransactions();
        if (!$bitpayTransactions) {
            return;
        }

        foreach ($bitpayTransactions as $bitpayTransaction) {
            $transaction = new InvoiceTransaction([
                'amount' => $bitpayTransaction['amount'] ?? null,
                'confirmations' => $bitpayTransaction['confirmations'] ?? null,
                'received_time' => $bitpayTransaction['receivedTime']
                    ? new \DateTimeImmutable($bitpayTransaction['receivedTime']) : null,
                'txid' => $bitpayTransaction['txid'] ?? null,
            ]);
            $transaction->invoice()->associate($invoice);
            $transaction->save();

            $bitpayExRates = $bitpayTransaction['exRates'] ?? null;
            if (!$bitpayExRates) {
                continue;
            }

            $exRatesToSave = [];
            foreach ($bitpayExRates as $currency => $amount) {
                $exRate = new InvoiceTransactionExRate([
                    'currency' => $currency,
                    'amount' => $amount
                ]);
                $exRatesToSave[] = $exRate;
            }

            $transaction->invoiceTransactionExRates()->saveMany($exRatesToSave);
        }
    }
}
