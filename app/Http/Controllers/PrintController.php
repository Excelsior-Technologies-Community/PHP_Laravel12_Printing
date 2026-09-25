<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Rawilk\Printing\Facades\Printing;
use Throwable;

class PrintController extends Controller
{
    /**
     * Display printing dashboard.
     */
    public function dashboard(Request $request)
    {
        $query = PrintJob::query();

        // Search by order ID, customer or printer name.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('printer_name', 'like', "%{$search}%");
            });
        }

        // Filter by status.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date.
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $jobs = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalJobs = PrintJob::count();

        $successfulJobs = PrintJob::where(
            'status',
            'success'
        )->count();

        $failedJobs = PrintJob::where(
            'status',
            'failed'
        )->count();

        $pendingJobs = PrintJob::where(
            'status',
            'pending'
        )->count();

        $todayJobs = PrintJob::whereDate(
            'created_at',
            today()
        )->count();

        $totalAmount = PrintJob::where(
            'status',
            'success'
        )->sum('total');

        $printers = $this->getPrinters();

        return view(
            'printing.dashboard',
            compact(
                'jobs',
                'totalJobs',
                'successfulJobs',
                'failedJobs',
                'pendingJobs',
                'todayJobs',
                'totalAmount',
                'printers'
            )
        );
    }

    /**
     * Generate invoice preview.
     */
    public function preview()
    {
        $data = $this->invoiceData();

        return view(
            'invoice-preview',
            $data
        );
    }

    /**
     * Download invoice PDF.
     */
    public function downloadInvoice()
    {
        $data = $this->invoiceData();

        $pdf = Pdf::loadView(
            'invoice',
            $data
        );

        return $pdf->download(
            'invoice-' . $data['order_id'] . '.pdf'
        );
    }

    /**
     * Print invoice using selected printer.
     */
    public function printInvoice(Request $request)
    {
        $request->validate([
            'printer_id' => [
                'required',
                'string',
            ],
        ]);

        $data = $this->invoiceData();

        $printerId = $request->printer_id;

        $printerName = $this->findPrinterName(
            $printerId
        );

        $fileName = 'invoice-' . $data['order_id'] . '.pdf';

        $directory = storage_path('app/invoices');

        if (!File::exists($directory)) {
            File::makeDirectory(
                $directory,
                0755,
                true
            );
        }

        $filePath = $directory .
            DIRECTORY_SEPARATOR .
            $fileName;

        try {
            /*
             * Generate PDF invoice.
             */
            $pdf = Pdf::loadView(
                'invoice',
                $data
            );

            file_put_contents(
                $filePath,
                $pdf->output()
            );

            /*
             * Create print job history record.
             */
            $printJob = PrintJob::create([
                'order_id' => $data['order_id'],
                'customer' => $data['customer'],
                'total' => $data['total'],
                'printer_id' => $printerId,
                'printer_name' => $printerName,
                'status' => 'pending',
                'file_name' => $fileName,
                'file_path' => $filePath,
            ]);

            /*
             * Send PDF to PrintNode.
             */
            Printing::newPrintTask()
                ->printer((int) $printerId)
                ->file($filePath)
                ->send();

            /*
             * Mark job as successful.
             */
            $printJob->update([
                'status' => 'success',
                'printed_at' => now(),
            ]);

            return redirect()
                ->route('printing.dashboard')
                ->with(
                    'success',
                    'Invoice sent successfully to '
                    . $printerName
                    . '.'
                );
        } catch (Throwable $e) {

            /*
             * Store failed print job.
             */
            if (isset($printJob)) {

                $printJob->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

            } else {

                PrintJob::create([
                    'order_id' => $data['order_id'],
                    'customer' => $data['customer'],
                    'total' => $data['total'],
                    'printer_id' => $printerId,
                    'printer_name' => $printerName,
                    'status' => 'failed',
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'error_message' => $e->getMessage(),
                ]);
            }

            return redirect()
                ->route('printing.dashboard')
                ->with(
                    'error',
                    'Printing failed: '
                    . $e->getMessage()
                );
        }
    }

    /**
     * Display available PrintNode printers.
     */
    public function printers()
    {
        $printers = $this->getPrinters();

        return view(
            'printing.printers',
            compact('printers')
        );
    }

    /**
     * Get and normalize printers from PrintNode.
     */
    private function getPrinters()
    {
        try {
            $printers = Printing::printers();

            $normalizedPrinters = [];

            foreach ($printers as $printer) {

                /*
                 * Convert printer object to array.
                 */
                if (is_object($printer)) {

                    if (method_exists(
                        $printer,
                        'toArray'
                    )) {
                        $printer = $printer->toArray();

                    } else {
                        $printer = get_object_vars(
                            $printer
                        );
                    }
                }

                /*
                 * Make sure we always have an array.
                 */
                if (!is_array($printer)) {
                    continue;
                }

                /*
                 * Extract printer information.
                 */
                $id = $this->getPrinterValue(
                    $printer,
                    [
                        'id',
                        'printerId',
                        'printer_id',
                    ]
                );

                $name = $this->getPrinterValue(
                    $printer,
                    [
                        'name',
                        'printerName',
                        'printer_name',
                    ],
                    'Unnamed Printer'
                );

                $state = $this->getPrinterValue(
                    $printer,
                    [
                        'state',
                        'status',
                        'printerState',
                        'printer_status',
                    ],
                    'Unknown'
                );

                $computer = $this->getPrinterValue(
                    $printer,
                    [
                        'computer',
                        'computerName',
                        'computer_name',
                    ],
                    'Unknown'
                );

                $description = $this->getPrinterValue(
                    $printer,
                    [
                        'description',
                    ],
                    ''
                );

                $normalizedPrinters[] = [
                    'id' => $id,
                    'name' => $name,
                    'state' => $state,
                    'computer' => $computer,
                    'description' => $description,
                    'raw' => $printer,
                ];
            }

            return collect($normalizedPrinters);

        } catch (Throwable $e) {

            return collect();
        }
    }

    /**
     * Get a printer value from possible API field names.
     */
    private function getPrinterValue(
        array $printer,
        array $keys,
        $default = null
    ) {
        foreach ($keys as $key) {

            if (
                array_key_exists($key, $printer)
                && $printer[$key] !== null
                && $printer[$key] !== ''
            ) {
                return $printer[$key];
            }
        }

        return $default;
    }

    /**
     * Find printer name using printer ID.
     */
    private function findPrinterName(
        string $printerId
    ): string {

        $printers = $this->getPrinters();

        foreach ($printers as $printer) {

            if (
                (string) $printer['id']
                === (string) $printerId
            ) {
                return $printer['name'];
            }
        }

        return 'Selected Printer';
    }

    /**
     * Demo invoice data.
     */
private function invoiceData(): array
{
    $lastOrderId = PrintJob::orderByDesc('id')->value('order_id');

    $nextOrderId = $lastOrderId
        ? ((int) $lastOrderId + 1)
        : 101;

    return [
        'order_id' => $nextOrderId,
        'customer' => 'Harry',
        'invoice_date' => now()->format('d M Y'),

        'items' => [
            [
                'name' => 'Product 1',
                'quantity' => 1,
                'price' => 1000,
            ],
            [
                'name' => 'Product 2',
                'quantity' => 1,
                'price' => 500,
            ],
        ],

        'total' => 1500,
    ];
}
}