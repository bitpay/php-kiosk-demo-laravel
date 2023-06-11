<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\SseConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use App\Features\Shared\Configuration\Mode;
use Illuminate\Http\Request;

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

    public function execute(Request $request): View|Factory
    {
        $design = $this->bitPayConfiguration->getMode();

        return view('pages.invoice.create' . ucfirst($design->value) . 'InvoiceForm', [
            'configuration' => $this->bitPayConfiguration,
            'sseUrl' => $this->sseConfiguration->publicUrl(),
            'sseTopic' => SendUpdateInvoiceEventStream::TOPIC,
            'errorMessage' => $request->get('errorMessage')
        ]);
    }
}
