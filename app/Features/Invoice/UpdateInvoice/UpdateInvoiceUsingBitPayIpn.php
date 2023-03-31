<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Shared\Exceptions\MissingInvoice;
use App\Features\Shared\Logger;
use App\Features\Shared\BitPayClientFactory;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;
use App\Models\Invoice\InvoiceRepositoryInterface;

class UpdateInvoiceUsingBitPayIpn
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private SendUpdateInvoiceEventStream $sendUpdateInvoiceEventStream;
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
        SendUpdateInvoiceEventStream $sendUpdateInvoiceEventStream,
        Logger $logger
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayUpdateMapper = $bitPayUpdateMapper;
        $this->bitPayClientFactory = $bitPayClientFactory;
        $this->sendUpdateInvoiceEventStream = $sendUpdateInvoiceEventStream;
        $this->logger = $logger;
        $this->updateInvoiceValidator = $updateInvoiceValidator;
        $this->bitPayConfiguration = $bitPayConfiguration;
    }

    public function execute(string $uuid, array $data): void
    {
        $invoice = $this->invoiceRepository->findOneByUuid($uuid);
        if (!$invoice) {
            throw new MissingInvoice('Missing invoice');
        }

        try {
            $client = $this->bitPayClientFactory->create();
            $bitPayInvoice = $client->getInvoice(
                $invoice->getBitpayId(),
                $this->bitPayConfiguration->getFacade(),
                $this->bitPayConfiguration->isSignRequest()
            );

            $updateInvoiceData = $this->bitPayUpdateMapper->execute($data)->toArray();
            $this->updateInvoiceValidator->execute($data, $bitPayInvoice);
            $this->updateInvoice($invoice, $updateInvoiceData);

            $this->sendUpdateInvoiceEventStream($invoice, $data);
        } catch (\Exception|\TypeError $e) {
            $this->logger->error('INVOICE_UPDATE_FAIL', 'Failed to update invoice', [
                'id' => $invoice->id
            ]);
            throw new \RuntimeException($e->getMessage());
        }
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
                $availableCurrencies[$paymentCurrency->getCurrencyCode()] = $paymentCurrency;
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

    private function sendUpdateInvoiceEventStream(Invoice $invoice, array $data): void
    {
        $eventName = $data['event'] ?? null;

        $this->sendUpdateInvoiceEventStream->execute(
            $invoice,
            $this->getEventMessageTypeFromEventName($eventName),
            $this->getEventMessageFromEventName($invoice->getBitpayId(), $eventName)
        );
    }

    private function getEventMessageTypeFromEventName(?string $eventName): ?UpdateInvoiceEventType
    {
        return match ($eventName) {
            null, 'invoice_manuallyNotified', 'invoice_refundComplete' => null,
            'invoice_paidInFull', 'invoice_confirmed', 'invoice_completed' => UpdateInvoiceEventType::SUCCESS,
            'invoice_expired', 'invoice_failedToConfirm', 'invoice_declined' => UpdateInvoiceEventType::ERROR,
            default => null
        };
    }

    private function getEventMessageFromEventName(string $invoiceId, ?string $eventName): ?string
    {
        return match($eventName) {
            null, 'invoice_manuallyNotified', 'invoice_refundComplete' => null,
            'invoice_paidInFull' => sprintf('Invoice %s has been paid in full.', $invoiceId),
            'invoice_expired' => sprintf('Invoice %s has expired.', $invoiceId),
            'invoice_confirmed' => sprintf('Invoice %s has been confirmed.', $invoiceId),
            'invoice_completed' => sprintf('Invoice %s is complete.', $invoiceId),
            'invoice_failedToConfirm' => sprintf('Invoice %s has failed to confirm.', $invoiceId),
            'invoice_declined' => sprintf('Invoice %s has been declined.', $invoiceId),
            default => null
        };
    }
}
