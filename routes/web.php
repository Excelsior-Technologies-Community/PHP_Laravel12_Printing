<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Printing Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/printing',
    [PrintController::class, 'dashboard']
)->name('printing.dashboard');


/*
|--------------------------------------------------------------------------
| Invoice Preview
|--------------------------------------------------------------------------
*/

Route::get(
    '/invoice/preview',
    [PrintController::class, 'preview']
)->name('invoice.preview');


/*
|--------------------------------------------------------------------------
| Invoice PDF Download
|--------------------------------------------------------------------------
*/

Route::get(
    '/invoice/download',
    [PrintController::class, 'downloadInvoice']
)->name('invoice.download');


/*
|--------------------------------------------------------------------------
| Print Invoice
|--------------------------------------------------------------------------
*/

Route::post(
    '/print',
    [PrintController::class, 'printInvoice']
)->name('printing.print');


/*
|--------------------------------------------------------------------------
| Printer Management
|--------------------------------------------------------------------------
*/

Route::get(
    '/printers',
    [PrintController::class, 'printers']
)->name('printing.printers');