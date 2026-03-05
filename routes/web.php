<?php

use Illuminate\Support\Facades\Route;
use Rawilk\Printing\Facades\Printing; 
use App\Http\Controllers\PrintController;

// Route to generate the invoice PDF and send it to the printer
Route::get('/print', [PrintController::class, 'printInvoice']);

// Route to retrieve and display the list of available printers from PrintNode
Route::get('/printers', function () { 
    return Printing::printers(); 
});