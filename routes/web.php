<?php

use App\Http\Controllers\Invoice\CreateInvoiceController;
use App\Http\Controllers\Invoice\GetInvoiceFormController;
use App\Http\Controllers\Invoice\GetInvoicesController;
use App\Http\Controllers\Invoice\GetInvoiceViewController;
use App\Http\Controllers\Invoice\UpdateInvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [GetInvoiceFormController::class, 'execute'])->name('invoiceForm');
Route::get('/invoices', [GetInvoicesController::class, 'execute'])->name('getInvoices');
Route::post('/invoices', [CreateInvoiceController::class, 'execute'])->name('createInvoice');
Route::get('/invoices/{id}', [GetInvoiceViewController::class, 'execute'])->name('invoiceView');
Route::post('/invoices/{uuid}', [UpdateInvoiceController::class, 'execute'])->name('updateInvoice');
