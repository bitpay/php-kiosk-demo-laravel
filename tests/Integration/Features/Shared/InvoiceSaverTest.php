<?php

declare(strict_types=1);

namespace Tests\Integration\Features\Shared;

use App\Features\Shared\DateTimeImmutableCreator;
use App\Features\Shared\InvoiceSaver;
use App\Models\Invoice\InvoiceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use Tests\Integration\ExampleSdkInvoice;
use Tests\TestCase;

class InvoiceSaverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @throws \JsonException
     */
    public function it_should_save_bitpay_invoice_to_db(): void
    {
        $bitpayInvoice = (new ExampleSdkInvoice())->get();
        $uuid = '1234';
        $applicationInvoice = $this->getTestedClass()->fromSdkModel($bitpayInvoice, $uuid);

        /** @var InvoiceTransaction $transaction */
        $transaction = $applicationInvoice->invoiceTransactions()->getResults()[0];
        $invoicePayment = $applicationInvoice->getInvoicePayment();
        $invoiceBuyer = $applicationInvoice->getInvoiceBuyer();
        $invoiceBuyerProvidedInfo = $invoiceBuyer->getInvoiceBuyerProvidedInfo();
        $invoiceRefund = $applicationInvoice->getInvoiceRefund();
        $refundInfo = $invoiceRefund->getInvoiceRefundInfo();

        Assert::assertEquals($uuid, $applicationInvoice->uuid);
        Assert::assertEquals($bitpayInvoice->getCurrency(), $applicationInvoice->currency_code);
        Assert::assertEquals($bitpayInvoice->getGuid(), $applicationInvoice->bitpay_guid);
        Assert::assertEquals($bitpayInvoice->getToken(), $applicationInvoice->token);
        Assert::assertEquals($bitpayInvoice->getPrice(), $applicationInvoice->price);
        Assert::assertEquals($bitpayInvoice->getPosData(), $applicationInvoice->pos_data_json);
        Assert::assertEquals($bitpayInvoice->getTransactionSpeed(), $applicationInvoice->transaction_speed);
        Assert::assertEquals($bitpayInvoice->getRedirectURL(), $applicationInvoice->redirect_url);
        Assert::assertEquals($bitpayInvoice->getOrderId(), $applicationInvoice->bitpay_order_id);
        Assert::assertEquals($bitpayInvoice->getItemDesc(), $applicationInvoice->item_description);
        Assert::assertEquals($bitpayInvoice->getAcceptanceWindow(), $applicationInvoice->acceptance_window);
        Assert::assertEquals($bitpayInvoice->getCloseURL(), $applicationInvoice->close_url);
        Assert::assertEquals($bitpayInvoice->getAutoRedirect(), $applicationInvoice->auto_redirect);
        Assert::assertEquals($bitpayInvoice->getRefundAddresses(), json_decode($applicationInvoice->invoiceRefund->addresses_json, true, 512, JSON_THROW_ON_ERROR));
        Assert::assertEquals($bitpayInvoice->getId(), $applicationInvoice->bitpay_id);
        Assert::assertEquals($bitpayInvoice->getUrl(), $applicationInvoice->bitpay_url);
        Assert::assertEquals($bitpayInvoice->getStatus(), $applicationInvoice->status);
        Assert::assertEquals($bitpayInvoice->getLowFeeDetected(), $applicationInvoice->low_fee_detected);
        Assert::assertEquals(DateTimeImmutableCreator::fromTimestamp($bitpayInvoice->getInvoiceTime()), $applicationInvoice->created_date);
        Assert::assertEquals(DateTimeImmutableCreator::fromTimestamp((int)$bitpayInvoice->getExpirationTime()), $applicationInvoice->expiration_time);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[0]['description'], $applicationInvoice->invoiceItemizedDetails()->get()[0]->description);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[0]['isFee'], $applicationInvoice->invoiceItemizedDetails()->get()[0]->is_fee);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[0]['amount'], $applicationInvoice->invoiceItemizedDetails()->get()[0]->amount);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[1]['description'], $applicationInvoice->invoiceItemizedDetails()->get()[1]->description);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[1]['isFee'], $applicationInvoice->invoiceItemizedDetails()->get()[1]->is_fee);
        Assert::assertEquals($bitpayInvoice->getItemizedDetails()[1]['amount'], $applicationInvoice->invoiceItemizedDetails()->get()[1]->amount);
        Assert::assertEquals($bitpayInvoice->getTransactions()[0]['amount'], $transaction->amount);
        Assert::assertEquals($bitpayInvoice->getTransactions()[0]['confirmations'], $transaction->confirmations);
        $bitpayReceivedTime = new \DateTime($bitpayInvoice->getTransactions()[0]['receivedTime']);
        $transactionReceivedTime = $transaction->received_time;
        Assert::assertEquals($bitpayReceivedTime->format(\DateTimeInterface::ATOM), $transactionReceivedTime->format(\DateTimeInterface::ATOM));
        Assert::assertEquals($bitpayInvoice->getTransactions()[0]['txid'], $transaction->txid);
        Assert::assertEquals($bitpayInvoice->getTransactions()[0]['exRates']['EUR'], $transaction->invoiceTransactionExRates()->where('currency', 'EUR')->first()->amount);
        Assert::assertEquals($bitpayInvoice->getExceptionStatus(), $applicationInvoice->exception_status);
        Assert::assertEquals($bitpayInvoice->getTargetConfirmations(), $applicationInvoice->target_confirmations);
        Assert::assertEquals($bitpayInvoice->getRefundAddressRequestPending(), $applicationInvoice->invoiceRefund->address_request_pending);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedEmail(), $applicationInvoice->invoiceBuyer->buyer_provided_email);
        Assert::assertEquals($bitpayInvoice->getBillId(), $applicationInvoice->bill_id);
        Assert::assertEquals($bitpayInvoice->getTransactionCurrency(), $invoicePayment->transaction_currency);
        Assert::assertEquals($bitpayInvoice->getAmountPaid(), $invoicePayment->amount_paid);
        Assert::assertEquals($bitpayInvoice->getMerchantName(), $applicationInvoice->merchant_name);
        Assert::assertEquals($bitpayInvoice->getBitpayIdRequired(), $applicationInvoice->bitpay_id_required);
        Assert::assertEquals($bitpayInvoice->getIsCancelled(), $applicationInvoice->is_cancelled);
        $btcPaymentCurrency = $invoicePayment->paymentCurrencies()->where('currency_code', 'BTC')->first();
        $exchangeRateBtcUsd = $btcPaymentCurrency->getExchangeRates()->where('currency_code', 'USD')->first();
        Assert::assertEquals($bitpayInvoice->getPaymentTotals()->BTC, $btcPaymentCurrency->total);
        Assert::assertEquals($bitpayInvoice->getPaymentDisplayTotals()->BTC, $btcPaymentCurrency->display_total);
        Assert::assertEquals($bitpayInvoice->getPaymentSubTotals()->BTC, $btcPaymentCurrency->subtotal);
        Assert::assertEquals($bitpayInvoice->getPaymentDisplaySubTotals()->BTC, $btcPaymentCurrency->display_subtotal);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getName(), $invoiceBuyerProvidedInfo->name);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getPhoneNumber(), $invoiceBuyerProvidedInfo->phone_number);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getSelectedWallet(), $invoiceBuyerProvidedInfo->selected_wallet);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getEmailAddress(), $invoiceBuyerProvidedInfo->email_address);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getSelectedTransactionCurrency(), $invoiceBuyerProvidedInfo->selected_transaction_currency);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getSms(), $invoiceBuyerProvidedInfo->sms);
        Assert::assertEquals($bitpayInvoice->getBuyerProvidedInfo()->getSmsVerified(), $invoiceBuyerProvidedInfo->sms_verified);
        Assert::assertEquals($bitpayInvoice->getUniversalCodes()->getPaymentString(), $invoicePayment->universal_codes_payment_string);
        Assert::assertEquals($bitpayInvoice->getUniversalCodes()->getVerificationLink(), $invoicePayment->universal_codes_verification_link);
        Assert::assertEquals($bitpayInvoice->getSupportedTransactionCurrencies()->getBTC()->getEnabled(), $btcPaymentCurrency->getSupportedTransactionCurrency()->enabled);
        Assert::assertEquals($bitpayInvoice->getSupportedTransactionCurrencies()->getBTC()->getReason(), $btcPaymentCurrency->getSupportedTransactionCurrency()->reason);
        Assert::assertEquals($bitpayInvoice->getMinerFees()->getBTC()->getSatoshisPerByte(), $btcPaymentCurrency->getMinerFee()->satoshis_per_byte);
        Assert::assertEquals($bitpayInvoice->getMinerFees()->getBTC()->getFiatAmount(), $btcPaymentCurrency->getMinerFee()->fiat_amount);
        Assert::assertEquals($bitpayInvoice->getMinerFees()->getBTC()->getTotalFee(), $btcPaymentCurrency->getMinerFee()->total_fee);
        Assert::assertEquals($bitpayInvoice->getShopper()->getUser(), $applicationInvoice->shopper_user);
        Assert::assertEquals($bitpayInvoice->getRefundInfo()->getCurrency(), $refundInfo->currency_code);
        Assert::assertEquals($bitpayInvoice->getRefundInfo()->getAmounts()['BTC'], $refundInfo->invoiceRefundInfoAmounts()->where('currency_code', 'BTC')->first()->amount);
        Assert::assertEquals($bitpayInvoice->getRefundInfo()->getSupportRequest(), $refundInfo->support_request);
        Assert::assertEquals($bitpayInvoice->getExchangeRates()->BTC->USD, $exchangeRateBtcUsd->rate);
        Assert::assertEquals($bitpayInvoice->getUrl(), $applicationInvoice->bitpay_url);
        Assert::assertEquals($bitpayInvoice->getPaymentCodes()->BTC->BIP72b, $btcPaymentCurrency->currencyCodes()->first()->getAttribute('code_url'));
    }

    private function getTestedClass(): InvoiceSaver
    {
        return new InvoiceSaver();
    }
}
