<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Invoice\CreateInvoice;

use App\Features\Invoice\CreateInvoice\Validator\CreateInvoiceValidator;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\Configuration\Mode;
use App\Features\Shared\InvoiceSaver;
use App\Features\Shared\Logger;
use App\Features\Shared\UrlProvider;
use App\Features\Shared\UuidFactory;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use App\Shared\Exceptions\ValidationFailed;
use BitpaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Buyer;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

class CreateInvoice
{
    private BitPayConfigurationInterface $bitPayConfiguration;
    private BitPayClientFactory $bitPayClientFactory;
    private InvoiceSaver $invoiceSaver;
    private CreateInvoiceValidator $createInvoiceValidator;
    private UuidFactory $uuidFactory;
    private UrlProvider $urlProvider;
    private Logger $logger;

    public function __construct(
        BitPayConfigurationInterface $bitPayConfiguration,
        BitPayClientFactory $bitPayClientFactory,
        InvoiceSaver $invoiceSaver,
        CreateInvoiceValidator $createInvoiceValidator,
        UuidFactory $uuidFactory,
        UrlProvider $urlProvider,
        Logger $logger
    ) {
        $this->bitPayConfiguration = $bitPayConfiguration;
        $this->bitPayClientFactory = $bitPayClientFactory;
        $this->invoiceSaver = $invoiceSaver;
        $this->createInvoiceValidator = $createInvoiceValidator;
        $this->uuidFactory = $uuidFactory;
        $this->urlProvider = $urlProvider;
        $this->logger = $logger;
    }

    /**
     * @throws \JsonException
     * @throws \BitpaySDK\Exceptions\BitPayException
     * @throws ValidationFailed
     */
    public function execute(array $params): Invoice
    {
        try {
            $validatedParams = $this->createInvoiceValidator->execute($params);
            $uuid = $this->uuidFactory->create();
            $requestData = $this->createRequestData($validatedParams, $uuid);
            $bitpayInvoice = $this->createBitpayInvoice($requestData);

            $invoice = $this->invoiceSaver->fromSdkModel($bitpayInvoice, $uuid);
            $this->logger->info('INVOICE_CREATE_SUCCESS', 'Successfully created invoice', [
                'id' => $invoice->id
            ]);
        } catch (BitPayException | \JsonException $e) {
            $this->logger->error('INVOICE_CREATE_FAIL', 'Failed to create invoice', [
                "errorMessage" => $e->getMessage(),
                "stackTrace" => $e->getTraceAsString()
            ]);
            throw new \RuntimeException($e->getMessage());
        }

        return $invoice;
    }

    /**
     * @param array $validatedParams
     * @param string $uuid
     * @return BitPayInvoice
     * @throws \JsonException
     */
    private function createRequestData(array $validatedParams, string $uuid): BitPayInvoice
    {
        $price = $validatedParams['price'];
        $posDataJson = json_encode($validatedParams, JSON_THROW_ON_ERROR);
        $invoice = new BitPayInvoice((float)$price, $this->bitPayConfiguration->getCurrencyIsoCode());
        $notificationEmail = $this->bitPayConfiguration->getNotificationEmail();
        $notificationUrl = $this->getNotificationUrl($uuid);
        $invoiceMode = $this->bitPayConfiguration->getMode();

        $invoice->setOrderId((string)uniqid('', true));
        $invoice->setTransactionSpeed("medium");
        $invoice->setItemDesc($invoiceMode->value);
        $invoice->setPosData($posDataJson);
        $invoice->setNotificationURL($notificationUrl);
        $invoice->setExtendedNotifications(true);

        if ($invoiceMode === Mode::DONATION) {
            $invoice->setBuyer($this->getBuyer($validatedParams));
        }

        if ($notificationEmail) {
            $invoice->setNotificationEmail($notificationEmail);
        }

        return $invoice;
    }

    /**
     * @param BitPayInvoice $requestedInvoice
     * @return BitPayInvoice
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    private function createBitpayInvoice(BitPayInvoice $requestedInvoice): BitPayInvoice
    {
        $client = $this->bitPayClientFactory->create();

        $facade = $this->bitPayConfiguration->getFacade();
        $signRequest = $facade !== Facade::Pos;

        return $client->createInvoice($requestedInvoice, $facade, $signRequest);
    }

    private function getNotificationUrl(string $uuid): string
    {
        return sprintf("%s/invoices/%s", $this->urlProvider->applicationUrl(), $uuid);
    }

    private function getBuyer(array $validatedParams): Buyer
    {
        $buyer = new Buyer();
        $buyer->setName($validatedParams['buyerName'] ?? null);
        $buyer->setAddress1($validatedParams['buyerAddress1'] ?? null);
        $buyer->setLocality($validatedParams['buyerLocality'] ?? null);
        $buyer->setRegion($validatedParams['buyerRegion'] ?? null);
        $buyer->setPostalCode($validatedParams['buyerPostalCode'] ?? null);
        $buyer->setCountry('US');
        $buyer->setEmail($validatedParams['buyerEmail'] ?? null);
        $buyer->setPhone($validatedParams['buyerPhone'] ?? null);

        if (isset($validatedParams['buyerAddress2'])) {
            $buyer->setAddress2($validatedParams['buyerAddress2'] ?? null);
        }

        return $buyer;
    }
}
