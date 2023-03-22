<?php

declare(strict_types=1);

namespace App\Features\Invoice\UpdateInvoice;

use App\Exceptions\MissingInvoice;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoicePaymentCurrency;
use App\Repository\InvoiceRepositoryInterface;

class UpdateInvoice
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private SendUpdateInvoiceNotification $sendUpdateInvoiceNotification;
    private BitPayUpdateMapper $bitPayUpdateMapper;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        BitPayUpdateMapper $bitPayUpdateMapper,
        SendUpdateInvoiceNotification $sendUpdateInvoiceNotification
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->bitPayUpdateMapper = $bitPayUpdateMapper;
        $this->sendUpdateInvoiceNotification = $sendUpdateInvoiceNotification;
    }

    public function usingBitPayUpdateResponse(string $uuid, array $data): Invoice
    {
        $invoice = $this->invoiceRepository->findOneByUuid($uuid);
        if (!$invoice) {
            throw new MissingInvoice('Missing invoice');
        }

        $updateInvoiceData = $this->bitPayUpdateMapper->execute($data)->toArray();

        $this->updateInvoice($invoice, $updateInvoiceData);
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
    }
}
