<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace Tests\Integration;

use BitPaySDK\Model\Invoice\BuyerProvidedInfo;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Model\Invoice\ItemizedDetails;
use BitPaySDK\Model\Invoice\MinerFees;
use BitPaySDK\Model\Invoice\MinerFeesItem;
use BitPaySDK\Model\Invoice\RefundInfo;
use BitPaySDK\Model\Invoice\Shopper;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrencies;
use BitPaySDK\Model\Invoice\SupportedTransactionCurrency;
use BitPaySDK\Model\Invoice\UniversalCodes;

class ExampleSdkInvoice
{
    public static function create(): Invoice
    {
        $invoice = new Invoice();
        $invoice->setCurrency('USD');
        $invoice->setGuid('payment#1234');
        $invoice->setToken('8nPJSGgi7omxcbGGZ4KsSgqdi6juypBe9pVpSURDeAwx4VDQx1XfWPy5qqknDKT9KQ');
        $invoice->setPrice(23.54);
        $invoice->setPosData('{"store":"store-1","register":"2","reg_transaction_no":"test123","price":"23.54"}');
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
        $invoice->setAcceptanceWindow(11);
        $invoice->setCloseURL('http://test.com');
        $invoice->setAutoRedirect(true);
        $invoice->setRefundAddresses(['Test refund address']);
        $invoice->setId('12');
        $invoice->setUrl('http://test.com');
        $invoice->setStatus('pending');
        $invoice->setLowFeeDetected(false);
        $invoice->setInvoiceTime(1620734545366);
        $invoice->setExpirationTime(1620734880748);
        $invoice->setCurrentTime(1620733980807);
        $invoice->setTransactions(self::getTransactions());
        $invoice->setExceptionStatus('false');
        $invoice->setTargetConfirmations(6);
        $invoice->setRefundAddressRequestPending(false);
        $invoice->setBuyerProvidedEmail('test@email.com');
        $invoice->setBillId('34');
        $invoice->setExtendedNotifications(true);
        $invoice->setTransactionCurrency('BTC');
        $invoice->setAmountPaid(12);
        $invoice->setExchangeRates(self::getExampleExchangeRates());
        $invoice->setMerchantName('Merchant name');
        $invoice->setSelectedTransactionCurrency('BTC');
        $invoice->setBitpayIdRequired(true);
        $invoice->setForcedBuyerSelectedWallet('Forced Buyer Selected Wallet');
        $invoice->setIsCancelled(true);
        $invoice->setBuyerEmail('test@email.com');
        $invoice->setBuyerSms('Buyer sms');
        $invoice->setForcedBuyerSelectedTransactionCurrency('BTC');

        $paymentTotals = [];
        $paymentTotals['BTC'] = 29800;
        $paymentTotals['BCH'] = 700700;
        $paymentTotals['ETH'] = 2406000000000000;
        $paymentTotals['GUSD'] = 1000;
        $paymentTotals['PAX'] = 10000000000000000000;
        $paymentTotals['BUSD'] = 10000000000000000000;
        $paymentTotals['USDC'] = 10000000;
        $paymentTotals['XRP'] = 6668704;
        $paymentTotals['DOGE'] = 2077327700;
        $paymentTotals['DAI'] = 9990000000000000000;
        $paymentTotals['WBTC'] = 1750;

        $paymentDisplayTotals = [];
        $paymentDisplayTotals['BTC'] = "0.000298";
        $paymentDisplayTotals['BCH'] = "0.007007";
        $paymentDisplayTotals['ETH'] = "0.002406";
        $paymentDisplayTotals['GUSD'] = "10.00";
        $paymentDisplayTotals['PAX'] = "10.00";
        $paymentDisplayTotals['BUSD'] = "10.00";
        $paymentDisplayTotals['USDC'] = "10.00";
        $paymentDisplayTotals['XRP'] = "6.668704";
        $paymentDisplayTotals['DOGE'] = "20.773277";
        $paymentDisplayTotals['DAI'] = "9.99";
        $paymentDisplayTotals['WBTC'] = "0.00017";

        $paymentSubTotals = [];
        $paymentSubTotals['BTC'] = 17500;
        $paymentSubTotals['BCH'] = 700700;
        $paymentSubTotals['ETH'] = 2406000000000000;
        $paymentSubTotals['GUSD'] = 1000;
        $paymentSubTotals['PAX'] = 10000000000000000000;
        $paymentSubTotals['BUSD'] = 10000000000000000000;
        $paymentSubTotals['USDC'] = 10000000;
        $paymentSubTotals['XRP'] = 6668704;
        $paymentSubTotals['DOGE'] = 2077327700;
        $paymentSubTotals['DAI'] = 9990000000000000000;
        $paymentSubTotals['WBTC'] = 1750;

        $paymentDisplaySubTotals = [];
        $paymentDisplaySubTotals['BTC'] = "0.000175";
        $paymentDisplaySubTotals['BCH'] = "0.007007";
        $paymentDisplaySubTotals['ETH'] = "0.002406";
        $paymentDisplaySubTotals['GUSD'] = "10.00";
        $paymentDisplaySubTotals['PAX'] = "10.00";
        $paymentDisplaySubTotals['BUSD'] = "10.00";
        $paymentDisplaySubTotals['USDC'] = "10.00";
        $paymentDisplaySubTotals['XRP'] = "6.668704";
        $paymentDisplaySubTotals['DOGE'] = "20.773277";
        $paymentDisplaySubTotals['DAI'] = "9.99";
        $paymentDisplaySubTotals['WBTC'] = "0.000175";

        $invoice->setPaymentTotals($paymentTotals);
        $invoice->setPaymentDisplayTotals($paymentDisplayTotals);
        $invoice->setPaymentSubTotals($paymentSubTotals);
        $invoice->setPaymentDisplaySubTotals($paymentDisplaySubTotals);
        $invoice->setItemizedDetails(self::getInvoiceItemizedDetails());
        $paymentCodes = [
          "BTC" => [
              "BIP72b" => "bitcoin:?r=https://bitpay.com/i/KSnNNfoMDsbRzd1U9ypmVH"
          ]
        ];
        $invoice->setPaymentCodes($paymentCodes);

        $invoice->setBuyerProvidedInfo(self::getBuyerProvidedInfo());
        $invoice->setUniversalCodes(self::getUniversalCodes());
        $invoice->setSupportedTransactionCurrencies(self::getSupportedTransactionCurrencies());
        $invoice->setMinerFees(self::getMinerFees());
        $invoice->setShopper(self::getShopper());
        $invoice->setRefundInfo(self::getRefundInfo());
        $invoice->setExchangeRates(self::getExampleExchangeRates());
        $invoice->setUrl('https://test.bitpay.com/invoice?id=YUVJ8caCU1DLnUoc4nug4iN');

        return $invoice;
    }

    private static function getInvoiceItemizedDetails()
    {
      $exampleItemizedDetails1 = new ItemizedDetails;
      $exampleItemizedDetails1->setAmount(5.0);
      $exampleItemizedDetails1->setDescription('Item 1');
      $exampleItemizedDetails1->setIsFee(false);
      $exampleItemizedDetails2 = new ItemizedDetails;
      $exampleItemizedDetails2->setAmount(15.0);
      $exampleItemizedDetails2->setDescription('Item 2');
      $exampleItemizedDetails2->setIsFee(false);
      $itemizedDetails = array($exampleItemizedDetails1, $exampleItemizedDetails2);

      return $itemizedDetails;
    }

    private static function getBuyerProvidedInfo(): BuyerProvidedInfo
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

    private static function getExampleExchangeRates(): array
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

    private static function getUniversalCodes(): UniversalCodes
    {
        $universalCodes = new UniversalCodes();
        $universalCodes->setPaymentString('https://link.bitpay.com/i/KSnNNfoMDsbRzd1U9ypmVH');
        $universalCodes->setVerificationLink('https://link.bitpay.com/someLink');

        return $universalCodes;
    }

    private static function getSupportedTransactionCurrencies(): SupportedTransactionCurrencies
    {
        $supportedTransactionCurrencies = new SupportedTransactionCurrencies();
        $btc = new SupportedTransactionCurrency();
        $btc->setEnabled(true);
        $btc->setReason('someReason');
        $supportedTransactionCurrencies->setBTC($btc);

        return $supportedTransactionCurrencies;
    }

    private static function getMinerFees(): MinerFees
    {
        $minerFees = new MinerFees();
        $item = new MinerFeesItem();
        $item->setSatoshisPerByte(1.0);
        $item->setTotalFee(100);
        $item->setFiatAmount(0.02);

        $minerFees->setBTC($item);

        return $minerFees;
    }

    private static function getShopper(): Shopper
    {
        $shopper = new Shopper();
        $shopper->setUser('someUser');

        return $shopper;
    }

    private static function getRefundInfo(): RefundInfo
    {
        $refundInfo = new RefundInfo();
        $refundInfo->setAmounts([
            'BTC' => 12.42
        ]);
        $refundInfo->setCurrency('USD');
        $refundInfo->setSupportRequest('supportRequest');

        return $refundInfo;
    }

    private static function getExchangeRates(): \stdClass
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

    private static function getTransactions(): array
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
