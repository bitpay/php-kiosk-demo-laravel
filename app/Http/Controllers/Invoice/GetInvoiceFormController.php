<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\SseConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class GetInvoiceFormController extends Controller
{
    private BitPayConfigurationInterface $bitPayConfiguration;
    private SseConfiguration $sseConfiguration;

    public function __construct(
        BitPayConfigurationInterface $bitPayConfiguration,
        SseConfiguration $sseConfiguration,
    ) {
        $this->bitPayConfiguration = $bitPayConfiguration;
        $this->sseConfiguration = $sseConfiguration;
    }

    public function execute(): View|Factory
    {
        return view('pages.invoice.createInvoiceForm', [
            'configuration' => $this->bitPayConfiguration,
            'sseUrl' => $this->sseConfiguration->publicUrl(),
            'sseTopic' => SendUpdateInvoiceEventStream::TOPIC
        ]);
    }
}
