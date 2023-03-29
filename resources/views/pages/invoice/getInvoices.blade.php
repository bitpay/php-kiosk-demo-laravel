@php
    /** @var \App\Features\Shared\Configuration\BitPayConfiguration $configuration **/
    /** @var App\Models\Invoice\Invoice $invoice **/
@endphp

@extends('layouts.default')

@section('head_scripts')
    @include('includes.tailwindComponents')
@stop

@section('content')
    <body class="h-full">

    <div class="min-h-full">

        @include('includes.navigation')

        <main>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="px-4 py-8 sm:px-0">

                    @include('includes.header', ['headerTitle' => 'Invoices'])

                    <div class="px-6 lg:px-8">

                        <div class="mt-8 flow-root">
                            <div class="-my-2 -mx-6 overflow-x-auto lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <table class="min-w-full divide-y divide-gray-300">

                                        <thead>
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">ID</th>
                                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Price</th>
                                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Description</th>
                                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($invoices as $invoice)
                                                <tr onclick="window.location.href = '/invoices/{{ $invoice->id }}'" class="cursor-pointer" data-uuid="{{ $invoice->uuid }}">
                                                    <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $invoice->bitpay_id }}</td>
                                                    <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">${{ number_format($invoice->price, 2) }}</td>
                                                    <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">{{ $invoice->item_description }}</td>
                                                    <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500 status">
                                                        <span class="inline-flex items-center rounded-full px-3 py-0.5 text-sm font-medium capitalize grid-status-{{ $invoice->status }}">{{ $invoice->status }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('includes.pagination', ['lengthAwarePaginator' => $invoices])

                </div>
            </div>
        </main>
    </div>

    <script type="text/javascript" src="{{ URL::asset ('js/invoices/updateInvoiceFromGridView.js') }}"></script>
    <script type="text/javascript">new UpdateStatusSse('{{$sseUrl}}', '{{$sseTopic}}').execute()</script>
@stop
