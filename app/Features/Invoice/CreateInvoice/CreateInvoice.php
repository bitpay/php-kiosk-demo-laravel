<?php

declare(strict_types=1);

namespace App\Features\Invoice\CreateInvoice;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\InvoiceSaver;
use App\Features\Shared\Logger;
use App\Features\Shared\UrlProvider;
use App\Features\Shared\UuidFactory;
use App\Http\Services\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use BitpaySDK\Exceptions\BitPayException;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

class CreateInvoice
{
    private BitPayConfigurationInterface $bitPayConfiguration;
    private BitPayClientFactory $bitPayClientFactory;
    private InvoiceSaver $invoiceSaver;
    private UuidFactory $uuidFactory;
    private UrlProvider $urlProvider;
    private Logger $logger;

    public function __construct(
        BitPayConfigurationInterface $bitPayConfiguration,
        BitPayClientFactory $bitPayClientFactory,
        InvoiceSaver $invoiceSaver,
        UuidFactory $uuidFactory,
        UrlProvider $urlProvider,
        Logger $logger
    ) {
        $this->bitPayConfiguration = $bitPayConfiguration;
        $this->bitPayClientFactory = $bitPayClientFactory;
        $this->invoiceSaver = $invoiceSaver;
        $this->uuidFactory = $uuidFactory;
        $this->urlProvider = $urlProvider;
        $this->logger = $logger;
    }

    /**
     * @throws \JsonException
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    public function execute(array $params): Invoice
    {
        try {
            $validatedParams = $this->validateParams($params);
            $uuid = $this->uuidFactory->create();
            $requestData = $this->createRequestData($validatedParams, $uuid);
            $bitpayInvoice = $this->createBitpayInvoice($requestData);

            $invoice = $this->invoiceSaver->fromSdkModel($bitpayInvoice, $uuid);
            $this->logger->info('INVOICE_CREATE_SUCCESS', 'Successfully created invoice', [
                'id' => $invoice->id
            ]);
        } catch (BitPayException|\JsonException $e) {
            $this->logger->error('INVOICE_CREATE_FAIL', 'Failed to create invoice', [
                "errorMessage" => $e->getMessage(),
                "stackTrace" => $e->getTraceAsString()
            ]);
            throw new \RuntimeException($e->getMessage());
        }

        return $invoice;
    }

    private function validateParams(array $params): array
    {
        $requiredParametersName = [];

        $bitPayFields = $this->bitPayConfiguration->getDesign()->getPosData()->getFields();
        foreach ($bitPayFields as $field) {
            if ($field->isRequired() === true) {
                $requiredParametersName[] = $field->getName();
            }
        }

        foreach ($requiredParametersName as $requiredParameterName) {
            $value = $params[$requiredParameterName] ?? null;
            if (!$value) {
                throw new \RuntimeException('Missing required field ' . $requiredParameterName);
            }
        }

        $validatedParams = [];
        foreach ($bitPayFields as $bitPayField) {
            $parameterName = $bitPayField->getName();
            if (array_key_exists($parameterName, $params)) {
                $validatedParams[$parameterName] = $params[$parameterName];
            }
        }

        if (!array_key_exists('price', $validatedParams)) {
            throw new \RuntimeException('Missing price');
        }

        $validatedParams['price'] = number_format((float)$validatedParams['price'], 2);

        return $validatedParams;
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

        $invoice->setOrderId((string)uniqid('', true));
        $invoice->setTransactionSpeed("medium");
        $invoice->setItemDesc("Example");
        $invoice->setPosData($posDataJson);
        $invoice->setNotificationURL($notificationUrl);
        $invoice->setExtendedNotifications(true);

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
    private function createBitpayInvoice(BitPayInvoice $requestedInvoice): BitPayInvoice {
        $client = $this->bitPayClientFactory->create();

        $facade = $this->bitPayConfiguration->getFacade();
        $signRequest = $facade !== Facade::Pos;

        return $client->createInvoice($requestedInvoice, $facade, $signRequest);
    }

    private function getNotificationUrl(string $uuid): string
    {
        return sprintf("%s/invoices/%s", $this->urlProvider->applicationUrl(), $uuid);
    }
}
