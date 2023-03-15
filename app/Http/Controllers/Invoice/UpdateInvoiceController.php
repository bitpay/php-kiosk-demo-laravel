<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Features\Invoice\UpdateInvoice\UpdateInvoice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateInvoiceController extends Controller
{
    private UpdateInvoice $updateInvoice;

    public function __construct(UpdateInvoice $updateInvoice)
    {
        $this->updateInvoice = $updateInvoice;
    }

    public function execute(Request $request, string $uuid): Response
    {
        /** @var array $data */
        $data = $request->request->get('data');

        $this->updateInvoice->usingBitPayUpdateResponse($uuid, $data);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
