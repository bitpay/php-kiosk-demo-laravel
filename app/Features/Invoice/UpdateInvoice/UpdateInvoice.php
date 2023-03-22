<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Configuration\BitPayConfigurationInterface;
use App\Exceptions\MissingInvoice;
use App\Features\Shared\Logger;
use App\Http\Services\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;
use App\Repository\InvoiceRepositoryInterface;

class UpdateInvoice
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private SendUpdateInvoiceNotification $sendUpdateInvoiceNotification;
    private BitPayUpdateMapper $bitPayUpdateMapper;
    private BitPayClientFactory $bitPayClientFactory;
    private BitPayConfigurationInterface $bitPayConfiguration;
    private Logger $logger;
    private UpdateInvoiceValidator $updateInvoiceValidator;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        BitPayUpdateMapper $bitPayUpdateMapper,
        BitPayClientFactory $bitPayClientFactory,
        BitPayConfigurationInterface $bitPayConfiguration,
        UpdateInvoiceValidator $updateInvoiceValidator,
        SendUpdateInvoiceNotification $sendUpdateInvoiceNotification,
        Logger $logger
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayUpdateMapper = $bitPayUpdateMapper;
        $this->bitPayClientFactory = $bitPayClientFactory;
        $this->sendUpdateInvoiceNotification = $sendUpdateInvoiceNotification;
        $this->logger = $logger;
        $this->updateInvoiceValidator = $updateInvoiceValidator;
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function usingBitPayUpdateResponse(string $uuid, array $data): Invoice
    {
        $invoice = $this->invoiceRepository->findOneByUuid($uuid);
        if (!$invoice) {
            throw new MissingInvoice('Missing invoice');
        }

        $client = $this->bitPayClientFactory->create();
        $bitPayInvoice = $client->getInvoice(
            $invoice->bitpay_id,
            $this->bitPayConfiguration->getFacade(),
            $this->bitPayConfiguration->isSignRequest()
        );

        try {
            $updateInvoiceData = $this->bitPayUpdateMapper->execute($data)->toArray();
            $this->updateInvoiceValidator->execute($data, $bitPayInvoice);
            $this->updateInvoice($invoice, $updateInvoiceData);
        } catch (\Exception $e) {
            $this->logger->error('INVOICE_UPDATE_FAIL', 'Failed to update invoice', [
                'id' => $invoice->id
            ]);
            throw new \RuntimeException($e->getMessage());
        }

        $this->sendUpdateInvoiceNotification->execute($invoice);

        return $invoice;
    }

    private function updatePaymentCurrencies(Invoice $invoice, array $updateInvoiceData): void
    {
        $key = 'invoice_payment_currency';
        if (!array_key_exists($key, $updateInvoiceData)) {
            return;
        }

        $currenciesToUpdate = $updateInvoiceData[$key] ?? null;
        if (!$currenciesToUpdate) {
            return;
        }

        $availableCurrencies = [];
        $invoicePayment = $this->getInvoicePayment($invoice);

        $currencies = $invoicePayment->getPaymentCurrencies();
        if ($currencies !== null) {
            /** @var InvoicePaymentCurrency $paymentCurrency */
            foreach ($currencies as $paymentCurrency) {
                $availableCurrencies[$paymentCurrency->currency_code] = $paymentCurrency;
            }
        }

        foreach ($currenciesToUpdate as $currencyToUpdate) {
            $currencyCode = $currencyToUpdate['currency_code'] ?? null;
            if (!$currencyCode) {
                throw new \RuntimeException('Invalid format');
            }

            $invoicePaymentCurrency = $availableCurrencies[$currencyCode]
                ?? (new InvoicePaymentCurrency([]))->invoicePayment()->associate($invoicePayment);
            $invoicePaymentCurrency->fill($currencyToUpdate);
            $invoicePaymentCurrency->save();
        }

    }

    /**
     * @param Invoice $invoice
     * @return InvoicePayment
     */
    private function getInvoicePayment(Invoice $invoice): InvoicePayment
    {
        $invoicePayment = $invoice->getInvoicePayment();
        if ($invoicePayment) {
            return $invoicePayment;
        }

        $invoicePayment = new InvoicePayment();
        $invoicePayment->save();
        $invoice->invoicePayment()->associate($invoicePayment);
        $invoice->save();

        return $invoicePayment;
    }

    private function updatePayment(Invoice $invoice, array $updateInvoiceData): void
    {
        $key = 'invoice_payment';
        if (!array_key_exists($key, $updateInvoiceData)) {
            return;
        }

        $invoicePayment = $this->getInvoicePayment($invoice);
        $invoicePayment->fill($updateInvoiceData[$key]);
        $invoicePayment->save();
    }

    /**
     * @param Invoice $invoice
     * @param array $updateInvoiceData
     */
    private function updateInvoice(Invoice $invoice, array $updateInvoiceData): void
    {
        $invoice->update($updateInvoiceData);
        $this->updatePayment($invoice, $updateInvoiceData);
        $this->updatePaymentCurrencies($invoice, $updateInvoiceData);

        $this->logger->info('INVOICE_UPDATE_SUCCESS', 'Successfully updated invoice', [
            'id' => $invoice->id
        ]);
    }
}
