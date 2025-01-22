<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Http\Controllers\Invoice;

use App\Shared\Exceptions\MissingInvoice;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceUsingBitPayIpn;
use App\Features\Shared\Logger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Shared\Exceptions\SignatureVerificationFailed;

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

        $payload = json_decode($request->getContent(), true);
        $data = $payload['data'];
        $event = $payload['event'];

        $data['uuid'] = $uuid;
        $data['event'] = $event['name'] ?? null;

        try {
            $this->updateInvoice->execute($uuid, $data, $request->headers->all());
        } catch (MissingInvoice $e) {
            return response(null, Response::HTTP_NOT_FOUND);
        } catch (SignatureVerificationFailed $e) {
            return response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response('Unable to process update', Response::HTTP_BAD_REQUEST);
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
