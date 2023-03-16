@php
    /** @var \App\Configuration\BitPayConfiguration $configuration **/
    /** @var App\Models\Invoice\Invoice $invoice **/
@endphp

@extends('layouts.default')

@section('head_scripts')
    <style type="text/tailwindcss">
        @layer components {
            .grid-status-new {
                @apply bg-gray-100 text-gray-800;
            }

            .grid-status-paid {
                @apply bg-yellow-100 text-yellow-800;
            }

            .grid-status-confirmed {
                @apply bg-blue-100 text-blue-800;
            }

            .grid-status-complete {
                @apply bg-green-100 text-green-800;
            }

            .grid-status-expired, .grid-status-invalid {
                @apply bg-red-100 text-red-800;
            }
        }
    </style>
@stop

@section('content')

    <div class="min-h-full">
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            <img class="block h-8 w-auto lg:hidden" src="{{ $configuration->getDesign()->getLogo() }}">
                            <img class="hidden h-8 w-auto lg:block" src="{{ $configuration->getDesign()->getLogo() }}">
                        </div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <a href="/invoices" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" aria-current="page">Invoices</a>
                        </div>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu open: "hidden", Menu closed: "block" -->
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <!-- Menu open: "block", Menu closed: "hidden" -->
                            <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="sm:hidden" id="mobile-menu">
                <div class="space-y-1 pt-2 pb-3">
                    <a href="/invoices" class="bg-indigo-50 border-indigo-500 text-indigo-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium" aria-current="page">Invoices</a>
                </div>
            </div>
        </nav>

        <main>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Replace with your content -->
                <div class="px-4 py-8 sm:px-0">
                    <header>
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Invoice Details</h1>
                        </div>
                    </header>
                    <div class="px-6 lg:px-8">

                        <div class="mt-8 flow-root">
                            <div class="-my-2 -mx-6 overflow-x-auto lg:-mx-8">
                                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                        <div class="px-4 py-5 sm:px-6">
                                            <h3 class="text-lg font-medium leading-6 text-gray-900">General Information</h3>
                                        </div>
                                        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
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
@stop
