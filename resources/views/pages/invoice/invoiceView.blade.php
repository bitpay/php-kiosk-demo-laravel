@php
    /** @var \App\Features\Shared\Configuration\BitPayConfiguration $configuration **/
    /** @var App\Models\Invoice\Invoice $invoice **/
@endphp

@extends('layouts.default')

@section('head_scripts')
    @include('includes.tailwindComponents')
@stop

@section('content')

    <div class="min-h-full">

        @include('includes.navigation')

        <main>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div class="px-4 py-8 sm:px-0">
                    @include('includes.header', ['headerTitle' => 'Invoice Details'])

                    <div class="px-6 lg:px-8">

                        <div class="mt-8 flow-root">
                            <div class="-my-2 -mx-6 overflow-x-auto lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                        <div class="px-4 py-5 sm:px-6">
                                            <h3 class="text-lg font-medium leading-6 text-gray-900">General Information</h3>
                                        </div>
                                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0" data-uuid="{{ $invoice->uuid }}">
                                            <dl class="sm:divide-y sm:divide-gray-200">
                                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                        <span>{{ $invoice->bitpay_id }}</span>
                                                        <span class="inline-flex items-center rounded-full px-3 py-0.5 text-sm font-medium capitalize status grid-status-{{ $invoice->status }}">{{ $invoice->status }}</span>
                                                    </dd>
                                                </div>
                                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                                    <dt class="text-sm font-medium text-gray-500">Price</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                        <span>$</span>
                                                        <span>{{ number_format($invoice->price, 2) }}</span>
                                                    </dd>
                                                </div>
                                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                                    <dt class="text-sm font-medium text-gray-500">Date</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $invoice->created_date->format('Y-m-d H:i T') }}</dd>
                                                </div>
                                                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
                                                    <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $invoice->bitpay_order_id }}</dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script type="text/javascript" src="{{ URL::asset ('js/invoices/updateInvoiceFromDetailView.js') }}"></script>
    <script type="text/javascript">new UpdateStatusSse('{{$sseUrl}}', '{{$sseTopic}}').execute()</script>
@stop
