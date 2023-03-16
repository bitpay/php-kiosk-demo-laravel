<?php

declare(strict_types=1);

namespace Tests\Integration\Features\Shared;

use App\Features\Shared\InvoiceSaver;
use App\Models\Invoice\InvoiceTransaction;
use BitPaySDK\Model\Invoice\BuyerProvidedInfo;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Model\Invoice\MinerFees;
use BitPaySDK\Model\Invoice\MinerFeesItem;
use BitPaySDK\Model\Invoice\RefundInfo;
use BitPaySDK\Model\Invoice\Shopper;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrencies;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrency;
use BitPaySDK\Model\Invoice\TransactionDetails;
use BitPaySDK\Model\Invoice\UniversalCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
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
        $bitpayInvoice = $this->getExampleInvoice();
        $applicationInvoice = $this->getTestedClass()->fromSdkModel($bitpayInvoice);

        /** @var InvoiceTransaction $transaction */
        $transaction = $applicationInvoice->invoiceTransactions()->getResults()[0];
        $invoicePayment = $applicationInvoice->getInvoicePayment();
        $invoiceBuyer = $applicationInvoice->getInvoiceBuyer();
        $invoiceBuyerProvidedInfo = $invoiceBuyer->getInvoiceBuyerProvidedInfo();
        $invoiceRefund = $applicationInvoice->getInvoiceRefund();
        $refundInfo = $invoiceRefund->getInvoiceRefundInfo();

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
        Assert::assertEquals($this->fromTimestampWithMillisecondsToDateTimeImmutable($bitpayInvoice->getInvoiceTime()), $applicationInvoice->created_date);
        Assert::assertEquals($this->fromTimestampWithMillisecondsToDateTimeImmutable((int)$bitpayInvoice->getExpirationTime()), $applicationInvoice->expiration_time);
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

    private function getExampleInvoice(): Invoice
    {
        $invoice = new Invoice();
        $invoice->setCurrency('USD');
        $invoice->setGuid('payment#1234');
        $invoice->setToken('8nPJSGgi7omxcbGGZ4KsSgqdi6juypBe9pVpSURDeAwx4VDQx1XfWPy5qqknDKT9KQ');
        $invoice->setPrice(20.0);
        $invoice->setPosData('{ "ref" : 711454, "item" : "test_item" }');
        $invoice->setNotificationURL('http://test.com');
        $invoice->setTransactionSpeed('medium');
        $invoice->setFullNotifications(true);
        $invoice->setNotificationEmail('test@email.com');
        $invoice->setRedirectURL('http://test.com');
        $invoice->setOrderId('20210511_abcde');
        $invoice->setItemDesc('Test item desc');
        $invoice->setItemCode('Test item code');
        $invoice->setPhysical(true);
        $invoice->setPaymentCurrencies(['BTC']);
        $invoice->setAcceptanceWindow(1.1);
        $invoice->setCloseURL('http://test.com');
        $invoice->setAutoRedirect(true);
        $invoice->setRefundAddresses(['Test refund address']);
        $invoice->setId('12');
        $invoice->setUrl('http://test.com');
        $invoice->setStatus('pending');
        $invoice->setLowFeeDetected(false);
        $invoice->setInvoiceTime(1620734545366);
        $invoice->setExpirationTime('1620734880748');
        $invoice->setCurrentTime('1620733980807');
        $invoice->setTransactions($this->getTransactions());
        $invoice->setExceptionStatus(false);
        $invoice->setTargetConfirmations(6);
        $invoice->setRefundAddressRequestPending(false);
        $invoice->setBuyerProvidedEmail('test@email.com');
        $invoice->setBillId('34');
        $invoice->setExtendedNotifications(true);
        $invoice->setTransactionCurrency('BTC');
        $invoice->setAmountPaid(12);
        $invoice->setExchangeRates($this->getExampleExchangeRates());
        $invoice->setMerchantName('Merchant name');
        $invoice->setSelectedTransactionCurrency('BTC');
        $invoice->setBitpayIdRequired(true);
        $invoice->setForcedBuyerSelectedWallet('Forced Buyer Selected Wallet');
        $invoice->setPaymentString('Payment string');
        $invoice->setVerificationLink('http://test.com');
        $invoice->setIsCancelled(true);
        $invoice->setBuyerEmail('test@email.com');
        $invoice->setBuyerSms('Buyer sms');
        $invoice->setForcedBuyerSelectedTransactionCurrency('BTC');

        $paymentTotals = new \stdClass();
        $paymentTotals->BTC = 29800;
        $paymentTotals->BCH = 700700;
        $paymentTotals->ETH = 2406000000000000;
        $paymentTotals->GUSD = 1000;
        $paymentTotals->PAX = 10000000000000000000;
        $paymentTotals->BUSD = 10000000000000000000;
        $paymentTotals->USDC = 10000000;
        $paymentTotals->XRP = 6668704;
        $paymentTotals->DOGE = 2077327700;
        $paymentTotals->DAI = 9990000000000000000;
        $paymentTotals->WBTC = 1750;

        $paymentDisplayTotals = new \stdClass();
        $paymentDisplayTotals->BTC = "0.000298";
        $paymentDisplayTotals->BCH = "0.007007";
        $paymentDisplayTotals->ETH = "0.002406";
        $paymentDisplayTotals->GUSD = "10.00";
        $paymentDisplayTotals->PAX = "10.00";
        $paymentDisplayTotals->BUSD = "10.00";
        $paymentDisplayTotals->USDC = "10.00";
        $paymentDisplayTotals->XRP = "6.668704";
        $paymentDisplayTotals->DOGE = "20.773277";
        $paymentDisplayTotals->DAI = "9.99";
        $paymentDisplayTotals->WBTC = "0.00017";

        $paymentSubTotals = new \stdClass();
        $paymentSubTotals->BTC = 17500;
        $paymentSubTotals->BCH = 700700;
        $paymentSubTotals->ETH = 2406000000000000;
        $paymentSubTotals->GUSD = 1000;
        $paymentSubTotals->PAX = 10000000000000000000;
        $paymentSubTotals->BUSD = 10000000000000000000;
        $paymentSubTotals->USDC = 10000000;
        $paymentSubTotals->XRP = 6668704;
        $paymentSubTotals->DOGE = 2077327700;
        $paymentSubTotals->DAI = 9990000000000000000;
        $paymentSubTotals->WBTC = 1750;

        $paymentDisplaySubTotals = new \stdClass();
        $paymentDisplaySubTotals->BTC = "0.000175";
        $paymentDisplaySubTotals->BCH = "0.007007";
        $paymentDisplaySubTotals->ETH = "0.002406";
        $paymentDisplaySubTotals->GUSD = "10.00";
        $paymentDisplaySubTotals->PAX = "10.00";
        $paymentDisplaySubTotals->BUSD = "10.00";
        $paymentDisplaySubTotals->USDC = "10.00";
        $paymentDisplaySubTotals->XRP = "6.668704";
        $paymentDisplaySubTotals->DOGE = "20.773277";
        $paymentDisplaySubTotals->DAI = "9.99";
        $paymentDisplaySubTotals->WBTC = "0.000175";

        $invoice->setPaymentTotals($paymentTotals);
        $invoice->setPaymentDisplayTotals($paymentDisplayTotals);
        $invoice->setPaymentSubTotals($paymentSubTotals);
        $invoice->setPaymentDisplaySubTotals($paymentDisplaySubTotals);
        $invoice->setItemizedDetails([
            [
                "amount" => 5,
                "description" => "Item 1",
                "isFee" => false
            ],
            [
                "amount" => 15,
                "description" => "Item 2",
                "isFee" => false
            ]
        ]);

        $paymentCodeBtc = new \stdClass();
        $paymentCodeBtc->BIP72b = "bitcoin:?r=https://bitpay.com/i/KSnNNfoMDsbRzd1U9ypmVH";
        $paymentCodes = new \stdClass();
        $paymentCodes->BTC = $paymentCodeBtc;
        $invoice->setPaymentCodes($paymentCodes);

        $invoice->setBuyerProvidedInfo($this->getBuyerProvidedInfo());
        $invoice->setTransactionDetails($this->getTransactionDetails());
        $invoice->setUniversalCodes($this->getUniversalCodes());
        $invoice->setSupportedTransactionCurrencies($this->getSupportedTransactionCurrencies());
        $invoice->setMinerFees($this->getMinerFees());
        $invoice->setShopper($this->getShopper());
        $invoice->setRefundInfo($this->getRefundInfo());
        $invoice->setExchangeRates($this->getExchangeRates());
        $invoice->setUrl('https://test.bitpay.com/invoice?id=YUVJ8caCU1DLnUoc4nug4iN');

        return $invoice;
    }

    private function getBuyerProvidedInfo(): BuyerProvidedInfo
    {
        $info = new BuyerProvidedInfo();
        $info->setName('someName');
        $info->setPhoneNumber('123456');
        $info->setSelectedWallet("bitpay");
        $info->setEmailAddress("john@doe.com");
        $info->setSelectedTransactionCurrency("BTC");
        $info->setSms('23423874');
        $info->setSmsVerified(true);

        return $info;
    }

    private function getTransactionDetails(): TransactionDetails
    {
        $transactionDetails = new TransactionDetails();
        $transactionDetails->setAmount(12.2);
        $transactionDetails->setDescription('transaction description');
        $transactionDetails->setIsFee(true);

        return $transactionDetails;
    }

    private function getExampleExchangeRates(): array
    {
        return [
            "BTC" => [
                "USD" => 17120.09,
                "BCH" => 163.84429131974352,
                "ETH" => 13.299739755292292,
                "GUSD" => 17120.09,
                "PAX" => 17120.09,
                "BUSD" => 17120.09,
                "USDC" => 17120.09,
                "DOGE" => 188443.27083844703,
                "LTC" => 289.92531752751904,
                "MATIC" => 17878.1223893066,
                "USDC_m" => 17120.09
            ],
            "BCH" => [
                "USD" => 104.38999999999999,
                "BTC" => 0.006097902914889888,
                "ETH" => 0.08109535832200428,
                "GUSD" => 104.38999999999999,
                "PAX" => 104.38999999999999,
                "BUSD" => 104.38999999999999,
                "USDC" => 104.38999999999999,
                "DOGE" => 1149.0356092068141,
                "LTC" => 1.7678238780694326,
                "MATIC" => 109.01211361737676,
                "USDC_m" => 104.38999999999999
            ],
            "ETH" => [
                "USD" => 1286.54,
                "BTC" => 0.07515275424966411,
                "BCH" => 12.312565795769931,
                "GUSD" => 1286.54,
                "PAX" => 1286.54,
                "BUSD" => 1286.54,
                "USDC" => 1286.54,
                "DOGE" => 14161.129156709787,
                "LTC" => 21.787298899237936,
                "MATIC" => 1343.5045948203842,
                "USDC_m" => 1286.54
            ],
            "GUSD" => [
                "USD" => 1,
                "BTC" => 5.8414627022606464E-5,
                "BCH" => 0.009570293808019907,
                "ETH" => 7.768498737618955E-4,
                "PAX" => 1,
                "BUSD" => 1,
                "USDC" => 1,
                "DOGE" => 11.007142534790825,
                "LTC" => 0.01693480101608806,
                "MATIC" => 1.0442773600668336,
                "USDC_m" => 1
            ],
            "PAX" => [
                "USD" => 1,
                "BTC" => 5.8414627022606464E-5,
                "BCH" => 0.009570293808019907,
                "ETH" => 7.768498737618955E-4,
                "GUSD" => 1,
                "BUSD" => 1,
                "USDC" => 1,
                "DOGE" => 11.007142534790825,
                "LTC" => 0.01693480101608806,
                "MATIC" => 1.0442773600668336,
                "USDC_m" => 1
            ],
            "BUSD" => [
                "USD" => 1,
                "BTC" => 5.8414627022606464E-5,
                "BCH" => 0.009570293808019907,
                "ETH" => 7.768498737618955E-4,
                "GUSD" => 1,
                "PAX" => 1,
                "USDC" => 1,
                "DOGE" => 11.007142534790825,
                "LTC" => 0.01693480101608806,
                "MATIC" => 1.0442773600668336,
                "USDC_m" => 1
            ],
            "USDC" => [
                "USD" => 1,
                "BTC" => 5.8414627022606464E-5,
                "BCH" => 0.009570293808019907,
                "ETH" => 7.768498737618955E-4,
                "GUSD" => 1,
                "PAX" => 1,
                "BUSD" => 1,
                "DOGE" => 11.007142534790825,
                "LTC" => 0.01693480101608806,
                "MATIC" => 1.0442773600668336,
                "USDC_m" => 1
            ],
            "DOGE" => [
                "USD" => 0.09077389999999999,
                "BTC" => 5.302523511887377E-6,
                "BCH" => 8.687328930998182E-4,
                "ETH" => 7.051769275587492E-5,
                "GUSD" => 0.09077389999999999,
                "PAX" => 0.09077389999999999,
                "BUSD" => 0.09077389999999999,
                "USDC" => 0.09077389999999999,
                "LTC" => 0.0015372379339542762,
                "MATIC" => 0.09479312865497075,
                "USDC_m" => 0.09077389999999999
            ],
            "LTC" => [
                "USD" => 59.02,
                "BTC" => 0.0034476312868742336,
                "BCH" => 0.5648387405493349,
                "ETH" => 0.04584967954942708,
                "GUSD" => 59.02,
                "PAX" => 59.02,
                "BUSD" => 59.02,
                "USDC" => 59.02,
                "DOGE" => 649.6415524033546,
                "MATIC" => 61.63324979114453,
                "USDC_m" => 59.02
            ],
            "MATIC" => [
                "USD" => 0.9597999999999999,
                "BTC" => 5.6066359016297676E-5,
                "BCH" => 0.009185567996937507,
                "ETH" => 7.456205088366673E-4,
                "GUSD" => 0.9597999999999999,
                "PAX" => 0.9597999999999999,
                "BUSD" => 0.9597999999999999,
                "USDC" => 0.9597999999999999,
                "DOGE" => 10.564655404892232,
                "LTC" => 0.016254022015241322,
                "USDC_m" => 0.9597999999999999
            ],
            "USDC_m" => [
                "USD" => 1,
                "BTC" => 5.8414627022606464E-5,
                "BCH" => 0.009570293808019907,
                "ETH" => 7.768498737618955E-4,
                "GUSD" => 1,
                "PAX" => 1,
                "BUSD" => 1,
                "USDC" => 1,
                "DOGE" => 11.007142534790825,
                "LTC" => 0.01693480101608806,
                "MATIC" => 1.0442773600668336
            ]
        ];
    }

    private function getUniversalCodes(): UniversalCodes
    {
        $universalCodes = new UniversalCodes();
        $universalCodes->setPaymentString('https://link.bitpay.com/i/KSnNNfoMDsbRzd1U9ypmVH');
        $universalCodes->setVerificationLink('https://link.bitpay.com/someLink');

        return $universalCodes;
    }

    private function getSupportedTransactionCurrencies(): SupportedTransactionCurrencies
    {
        $supportedTransactionCurrencies = new SupportedTransactionCurrencies();
        $btc = new SupportedTransactionCurrency();
        $btc->setEnabled(true);
        $btc->setReason('someReason');
        $supportedTransactionCurrencies->setBTC($btc);

        return $supportedTransactionCurrencies;
    }

    private function getMinerFees(): MinerFees
    {
        $minerFees = new MinerFees();
        $item = new MinerFeesItem();
        $item->setSatoshisPerByte(1.0);
        $item->setTotalFee(100.0);
        $item->setFiatAmount(0.02);

        $minerFees->setBTC($item);

        return $minerFees;
    }

    private function getShopper(): Shopper
    {
        $shopper = new Shopper();
        $shopper->setUser('someUser');

        return $shopper;
    }

    private function getRefundInfo(): RefundInfo
    {
        $refundInfo = new RefundInfo();
        $refundInfo->setAmounts([
            'BTC' => 12.42
        ]);
        $refundInfo->setCurrency('USD');
        $refundInfo->setSupportRequest('supportRequest');

        return $refundInfo;
    }

    private function getExchangeRates(): \stdClass
    {
        $exchangeRates = new \stdClass();
        $btc = new \stdClass();

        $btc->USD = 55072.459995;
        $btc->EUR = 45287.42496;
        $btc->BCH = 40.884360404;
        $btc->ETH =  13.953840617367;
        $btc->GUSD = 55072.459995;
        $btc->PAX = 55072.459995;
        $btc->BUSD = 55072.459995;
        $btc->USDC = 55072.459995;
        $btc->XRP =  38907.543074032;
        $btc->DOGE = 113694.39064944;
        $btc->DAI = 55018.486859391;
        $btc->WBTC = 0.99835144307639;

        $exchangeRates->BTC = $btc;

        return $exchangeRates;
    }

    private function fromTimestampWithMillisecondsToDateTimeImmutable(?int $timestamp): ?\DateTimeImmutable
    {
        $convertedTimestamp = (int)($timestamp / 1000);
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($convertedTimestamp);

        return \DateTimeImmutable::createFromMutable($dateTime);
    }

    private function getTransactions(): array
    {
        return [
            [
                "amount" => 700700,
                "confirmations" => 6,
                "receivedTime" => "2021-05-10T18:05:12.472Z",
                "txid" => "0bdcb930b91d63eb10ec11e27a060d4dd25380d229c4b28a29a1456e0aa60128",
                "exRates" => [
                    "BCH" => 1,
                    "EUR" => 1112.66,
                    "BUSD" => 1425.3300000000002
                ]
            ]
        ];
    }
}
