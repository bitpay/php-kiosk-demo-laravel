<?php

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Shared\Exceptions\MissingInvoice;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceUsingBitPayIpn;
use App\Features\Shared\Logger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateInvoiceController extends Controller
{
    private UpdateInvoiceUsingBitPayIpn $updateInvoice;
    private Logger $logger;

    public function __construct(UpdateInvoiceUsingBitPayIpn $updateInvoice, Logger $logger)
    {
        $this->updateInvoice = $updateInvoice;
        $this->logger = $logger;
    }

    public function execute(Request $request, string $uuid): Response
    {
        $this->logger->info('IPN_RECEIVED', 'Received IPN', $request->request->all());

        /** @var array $data */
        $data = $request->request->get('data');
        $event = $request->request->get('event');

        $data['uuid'] = $uuid;
        $data['event'] = $event['name'] ?? null;

        try {
            $this->updateInvoice->execute($uuid, $data);
        } catch (MissingInvoice $e) {
            return response(null, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response('Unable to process update', Response::HTTP_BAD_REQUEST);
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
