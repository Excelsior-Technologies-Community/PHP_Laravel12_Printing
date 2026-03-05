<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Rawilk\Printing\Facades\Printing;

class PrintController extends Controller
{
    public function printInvoice()
    {
        $data = [
            'order_id' => 101,
            'customer' => 'Harry',
            'total' => 1500
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoice', $data);

        $filePath = storage_path('app/invoice.pdf');

        file_put_contents($filePath, $pdf->output());

        // Send print job
        Printing::newPrintTask()
            ->printer(75236937)
            ->file($filePath)
            ->send();

        return "Invoice sent to printer!";
    }
}