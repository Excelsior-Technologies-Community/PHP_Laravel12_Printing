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


/*
|--------------------------------------------------------------------------
| Print Job Details
|--------------------------------------------------------------------------
*/

Route::get(
    '/printing/jobs/{printJob}',
    [PrintController::class, 'show']
)->name('printing.show');


/*
|--------------------------------------------------------------------------
| Download Existing Invoice
|--------------------------------------------------------------------------
*/

Route::get(
    '/printing/jobs/{printJob}/download',
    [PrintController::class, 'downloadJob']
)->name('printing.job.download');


/*
|--------------------------------------------------------------------------
| Retry Failed Print
|--------------------------------------------------------------------------
*/

Route::post(
    '/printing/jobs/{printJob}/retry',
    [PrintController::class, 'retry']
)->name('printing.job.retry');


/*
|--------------------------------------------------------------------------
| Reprint Successful Job
|--------------------------------------------------------------------------
*/

Route::post(
    '/printing/jobs/{printJob}/reprint',
    [PrintController::class, 'reprint']
)->name('printing.job.reprint');


/*
|--------------------------------------------------------------------------
| Delete Print Job
|--------------------------------------------------------------------------
*/

Route::delete(
    '/printing/jobs/{printJob}',
    [PrintController::class, 'destroy']
)->name('printing.job.destroy');


/*
|--------------------------------------------------------------------------
| Bulk Delete Print Jobs
|--------------------------------------------------------------------------
*/

Route::delete(
    '/printing/jobs/bulk-delete',
    [PrintController::class, 'bulkDestroy']
)->name('printing.jobs.bulkDestroy');


Route::delete('/printing/bulk-delete', [PrintController::class, 'bulkDestroy'])
    ->name('printing.bulk-destroy');

    
/*
|--------------------------------------------------------------------------
| CSV Export
|--------------------------------------------------------------------------
*/

Route::get(
    '/printing/export',
    [PrintController::class, 'exportCsv']
)->name('printing.export');