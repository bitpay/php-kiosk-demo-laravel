<?php

use App\Http\Controllers\Invoice\CreateInvoiceController;
use App\Http\Controllers\Invoice\GetInvoiceFormController;
use App\Http\Controllers\Invoice\GetInvoiceViewController;
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
Route::post('/invoices', [CreateInvoiceController::class, 'execute'])->name('createInvoice');
Route::get('/invoices/{id}', [GetInvoiceViewController::class, 'execute'])->name('invoiceView');
