<?php

declare(strict_types=1);

namespace App\Features\Invoice;

use App\Configuration\BitPayConfigurationFactoryInterface;
use App\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\InvoiceSaver;
use App\Http\Services\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use BitPaySDK\Model\Facade;
use BitPaySDK\Model\Invoice\Invoice as BitPayInvoice;

class CreateInvoice
{
    private BitPayConfigurationFactoryInterface $bitPayConfigurationFactory;
    private BitPayClientFactory $bitPayClientFactory;
    private InvoiceSaver $invoiceSaver;

    public function __construct(
        BitPayConfigurationFactoryInterface $bitPayConfigurationFactory,
        BitPayClientFactory $bitPayClientFactory,
        InvoiceSaver $invoiceSaver
    ) {
        $this->bitPayConfigurationFactory = $bitPayConfigurationFactory;
        $this->bitPayClientFactory = $bitPayClientFactory;
        $this->invoiceSaver = $invoiceSaver;
    }

    /**
     * @throws \JsonException
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    public function execute(array $params): Invoice
    {
        /** @var BitPayConfigurationInterface $bitPayConfiguration */
        $bitPayConfiguration = $this->bitPayConfigurationFactory->create();
        $validatedParams = $this->validateParams($bitPayConfiguration, $params);
        $posDataJson = json_encode($validatedParams, JSON_THROW_ON_ERROR);

        $requestData = $this->createRequestData($validatedParams['price'], $bitPayConfiguration, $posDataJson);
        $bitpayInvoice = $this->createBitpayInvoice($bitPayConfiguration, $requestData);

        return $this->invoiceSaver->fromSdkModel($bitpayInvoice);
    }

    private function validateParams(BitPayConfigurationInterface $bitPayConfiguration, array $params): array
    {
        $requiredParametersName = [];

        $bitPayFields = $bitPayConfiguration->getDesign()->getPosData()->getFields();
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

        $validatedParams['price'] = number_format((float)$validatedParams['price'], 2);

        return $validatedParams;
    }

    /**
     * @param $price
     * @param BitPayConfigurationInterface $bitPayConfiguration
     * @param bool|string $posDataJson
     * @return array
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    private function createRequestData(
        $price,
        BitPayConfigurationInterface $bitPayConfiguration,
        bool|string $posDataJson
    ): BitPayInvoice {
        $invoice = new BitPayInvoice((float)$price, $bitPayConfiguration->getCurrencyIsoCode());
        $notificationEmail = $bitPayConfiguration->getNotificationEmail();

        $invoice->setOrderId((string)uniqid('', true));
        $invoice->setTransactionSpeed("medium");
        $invoice->setItemDesc("Example");
        $invoice->setPosData($posDataJson);

        if ($notificationEmail) {
            $invoice->setNotificationEmail($notificationEmail);
        }

        return $invoice;
    }

    /**
     * @param BitPayConfigurationInterface $bitPayConfiguration
     * @param BitPayInvoice $requestedInvoice
     * @return BitPayInvoice
     * @throws \BitpaySDK\Exceptions\BitPayException
     */
    private function createBitpayInvoice(
        BitPayConfigurationInterface $bitPayConfiguration,
        BitPayInvoice $requestedInvoice
    ): BitPayInvoice {
        $client = $this->bitPayClientFactory->create();

        $facade = $bitPayConfiguration->getFacade();
        $signRequest = $facade !== Facade::Pos;

        return $client->createInvoice($requestedInvoice, $facade, $signRequest);
    }
}