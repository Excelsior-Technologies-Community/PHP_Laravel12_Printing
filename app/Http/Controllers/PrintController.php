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
     * Printing dashboard.
     *
     * Added:
     * 1. Search
     * 2. Status filter
     * 3. Date filter
     * 4. Date range filter
     * 5. Printer filter
     * 6. Sorting
     * 7. Pagination
     */
    public function dashboard(Request $request)
    {
        $query = PrintJob::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('printer_name', 'like', "%{$search}%")
                    ->orWhere('printer_id', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | Printer filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('printer_id')) {
            $query->where(
                'printer_id',
                $request->printer_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Single date filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'order_id',
            'customer',
            'total',
            'status',
            'created_at',
            'printed_at',
        ];

        $sort = $request->get(
            'sort',
            'created_at'
        );

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower(
            $request->get(
                'direction',
                'asc'
            )
        );

        if (!in_array(
            $direction,
            ['asc', 'desc'],
            true
        )) {
            $direction = 'asc';
        }

        /*
        |--------------------------------------------------------------------------
        | Jobs
        |--------------------------------------------------------------------------
        */

        $jobs = $query
            ->orderBy($sort, $direction)
            ->paginate(5)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Additional statistics
        |--------------------------------------------------------------------------
        */

        $todaySuccessfulJobs = PrintJob::where(
            'status',
            'success'
        )
            ->whereDate(
                'created_at',
                today()
            )
            ->count();

        $todayFailedJobs = PrintJob::where(
            'status',
            'failed'
        )
            ->whereDate(
                'created_at',
                today()
            )
            ->count();

        $successRate = $totalJobs > 0
            ? round(
                ($successfulJobs / $totalJobs) * 100,
                2
            )
            : 0;

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
                'todaySuccessfulJobs',
                'todayFailedJobs',
                'successRate',
                'printers',
                'sort',
                'direction'
            )
        );
    }

    /**
     * Invoice preview.
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

        $fileName =
            'invoice-' .
            $data['order_id'] .
            '.pdf';

        $directory = storage_path(
            'app/invoices'
        );

        if (!File::exists($directory)) {
            File::makeDirectory(
                $directory,
                0755,
                true
            );
        }

        $filePath =
            $directory .
            DIRECTORY_SEPARATOR .
            $fileName;

        $printJob = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | Generate PDF
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Create pending job
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Send to PrintNode
            |--------------------------------------------------------------------------
            */

            Printing::newPrintTask()
                ->printer((int) $printerId)
                ->file($filePath)
                ->send();

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            $printJob->update([
                'status' => 'success',
                'printed_at' => now(),
                'error_message' => null,
            ]);

            return redirect()
                ->route(
                    'printing.dashboard'
                )
                ->with(
                    'success',
                    'Invoice sent successfully to ' .
                    $printerName .
                    '.'
                );

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Failed
            |--------------------------------------------------------------------------
            */

            if ($printJob) {

                $printJob->update([
                    'status' => 'failed',
                    'error_message' =>
                        $e->getMessage(),
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
                    'error_message' =>
                        $e->getMessage(),
                ]);
            }

            return redirect()
                ->route(
                    'printing.dashboard'
                )
                ->with(
                    'error',
                    'Printing failed: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * Printer management.
     */
    public function printers()
    {
        $printers = $this->getPrinters();

        return view(
            'printing.printers',
            compact('printers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 5. View Print Job Details
    |--------------------------------------------------------------------------
    */

    public function show(PrintJob $printJob)
    {
        return view(
            'printing.show',
            compact('printJob')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Download invoice from history
    |--------------------------------------------------------------------------
    */

    public function downloadJob(PrintJob $printJob)
    {
        if (
            !$printJob->file_path ||
            !File::exists($printJob->file_path)
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Invoice PDF file was not found.'
                );
        }

        return response()->download(
            $printJob->file_path,
            $printJob->file_name ??
            'invoice-' .
            $printJob->order_id .
            '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Retry Failed Print
    |--------------------------------------------------------------------------
    */

    public function retry(PrintJob $printJob)
    {
        if ($printJob->status !== 'failed') {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Only failed print jobs can be retried.'
                );
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Make sure PDF exists
            |--------------------------------------------------------------------------
            */

            if (
                !$printJob->file_path ||
                !File::exists(
                    $printJob->file_path
                )
            ) {
                $data = $this->invoiceData();

                $pdf = Pdf::loadView(
                    'invoice',
                    [
                        'order_id' =>
                            $printJob->order_id,
                        'customer' =>
                            $printJob->customer,
                        'invoice_date' =>
                            $printJob->created_at
                                ? $printJob->created_at
                                    ->format('d M Y')
                                : now()
                                    ->format('d M Y'),
                        'items' => [
                            [
                                'name' =>
                                    'Product 1',
                                'quantity' => 1,
                                'price' => 1000,
                            ],
                            [
                                'name' =>
                                    'Product 2',
                                'quantity' => 1,
                                'price' => 500,
                            ],
                        ],
                        'total' =>
                            $printJob->total,
                    ]
                );

                $directory =
                    storage_path(
                        'app/invoices'
                    );

                if (!File::exists($directory)) {
                    File::makeDirectory(
                        $directory,
                        0755,
                        true
                    );
                }

                $filePath =
                    $directory .
                    DIRECTORY_SEPARATOR .
                    (
                        $printJob->file_name ??
                        'invoice-' .
                        $printJob->order_id .
                        '.pdf'
                    );

                file_put_contents(
                    $filePath,
                    $pdf->output()
                );

                $printJob->update([
                    'file_path' => $filePath,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Set pending
            |--------------------------------------------------------------------------
            */

            $printJob->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Send again
            |--------------------------------------------------------------------------
            */

            Printing::newPrintTask()
                ->printer(
                    (int) $printJob->printer_id
                )
                ->file(
                    $printJob->file_path
                )
                ->send();

            /*
            |--------------------------------------------------------------------------
            | Mark successful
            |--------------------------------------------------------------------------
            */

            $printJob->update([
                'status' => 'success',
                'printed_at' => now(),
                'error_message' => null,
            ]);

            return redirect()
                ->route(
                    'printing.dashboard'
                )
                ->with(
                    'success',
                    'Failed print job retried successfully.'
                );

        } catch (Throwable $e) {

            $printJob->update([
                'status' => 'failed',
                'error_message' =>
                    $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Retry failed: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Reprint Successful Job
    |--------------------------------------------------------------------------
    */

    public function reprint(PrintJob $printJob)
    {
        if ($printJob->status !== 'success') {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Only successful print jobs can be reprinted.'
                );
        }

        if (
            !$printJob->file_path ||
            !File::exists($printJob->file_path)
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Invoice PDF file was not found.'
                );
        }

        try {

            Printing::newPrintTask()
                ->printer(
                    (int) $printJob->printer_id
                )
                ->file(
                    $printJob->file_path
                )
                ->send();

            $printJob->update([
                'printed_at' => now(),
                'status' => 'success',
                'error_message' => null,
            ]);

            return redirect()
                ->route(
                    'printing.dashboard'
                )
                ->with(
                    'success',
                    'Invoice reprinted successfully.'
                );

        } catch (Throwable $e) {

            $printJob->update([
                'status' => 'failed',
                'error_message' =>
                    $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Reprint failed: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 9. Delete Individual Job
    |--------------------------------------------------------------------------
    */

    public function destroy(PrintJob $printJob)
    {
        /*
        |--------------------------------------------------------------------------
        | Delete stored PDF
        |--------------------------------------------------------------------------
        */

        if (
            $printJob->file_path &&
            File::exists(
                $printJob->file_path
            )
        ) {
            File::delete(
                $printJob->file_path
            );
        }

        $printJob->delete();

        return redirect()
            ->route(
                'printing.dashboard'
            )
            ->with(
                'success',
                'Print job deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 10. Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDestroy(
        Request $request
    ) {
        $request->validate([
            'job_ids' => [
                'required',
                'array',
            ],
            'job_ids.*' => [
                'integer',
                'exists:print_jobs,id',
            ],
        ]);

        $jobs = PrintJob::whereIn(
            'id',
            $request->job_ids
        )->get();

        foreach ($jobs as $job) {

            if (
                $job->file_path &&
                File::exists(
                    $job->file_path
                )
            ) {
                File::delete(
                    $job->file_path
                );
            }

            $job->delete();
        }

        return redirect()
            ->route(
                'printing.dashboard'
            )
            ->with(
                'success',
                $jobs->count() .
                ' print job(s) deleted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CSV Export
    |--------------------------------------------------------------------------
    */

    public function exportCsv(Request $request)
    {
        $query = PrintJob::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->search
            );

            $query->where(function ($q) use ($search) {

                $q->where(
                    'order_id',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'customer',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'printer_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'printer_id',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Printer
        |--------------------------------------------------------------------------
        */

        if ($request->filled('printer_id')) {
            $query->where(
                'printer_id',
                $request->printer_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $jobs = $query
            ->oldest()
            ->get();

        $fileName =
            'print-jobs-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv; charset=UTF-8',
            'Content-Disposition' =>
                'attachment; filename="' .
                $fileName .
                '"',
        ];

        $callback = function () use ($jobs) {

            $file = fopen(
                'php://output',
                'w'
            );

            /*
            |--------------------------------------------------------------------------
            | UTF-8 BOM
            |--------------------------------------------------------------------------
            */

            fprintf(
                $file,
                chr(0xEF) .
                chr(0xBB) .
                chr(0xBF)
            );

            fputcsv(
                $file,
                [
                    'ID',
                    'Order ID',
                    'Customer',
                    'Total',
                    'Printer ID',
                    'Printer Name',
                    'Status',
                    'File Name',
                    'Printed At',
                    'Created At',
                    'Error',
                ]
            );

            foreach ($jobs as $job) {

                fputcsv(
                    $file,
                    [
                        $job->id,
                        $job->order_id,
                        $job->customer,
                        $job->total,
                        $job->printer_id,
                        $job->printer_name,
                        $job->status,
                        $job->file_name,
                        $job->printed_at
                            ? $job->printed_at
                                ->format(
                                    'Y-m-d H:i:s'
                                )
                            : '',
                        $job->created_at
                            ? $job->created_at
                                ->format(
                                    'Y-m-d H:i:s'
                                )
                            : '',
                        $job->error_message,
                    ]
                );
            }

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get PrintNode Printers
    |--------------------------------------------------------------------------
    */

    private function getPrinters()
    {
        try {

            $printers = Printing::printers();

            $normalizedPrinters = [];

            foreach ($printers as $printer) {

                if (is_object($printer)) {

                    if (
                        method_exists(
                            $printer,
                            'toArray'
                        )
                    ) {
                        $printer =
                            $printer->toArray();
                    } else {
                        $printer =
                            get_object_vars(
                                $printer
                            );
                    }
                }

                if (!is_array($printer)) {
                    continue;
                }

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

                $description =
                    $this->getPrinterValue(
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
                    'description' =>
                        $description,
                    'raw' => $printer,
                ];
            }

            return collect(
                $normalizedPrinters
            );

        } catch (Throwable $e) {

            return collect();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Printer value helper
    |--------------------------------------------------------------------------
    */

    private function getPrinterValue(
        array $printer,
        array $keys,
        $default = null
    ) {
        foreach ($keys as $key) {

            if (
                array_key_exists(
                    $key,
                    $printer
                ) &&
                $printer[$key] !== null &&
                $printer[$key] !== ''
            ) {
                return $printer[$key];
            }
        }

        return $default;
    }

    /*
    |--------------------------------------------------------------------------
    | Find printer name
    |--------------------------------------------------------------------------
    */

    private function findPrinterName(
        string $printerId
    ): string {

        $printers = $this->getPrinters();

        foreach ($printers as $printer) {

            if (
                (string) $printer['id'] ===
                (string) $printerId
            ) {
                return $printer['name'];
            }
        }

        return 'Selected Printer';
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice data
    |--------------------------------------------------------------------------
    */

    private function invoiceData(): array
    {
        $lastOrderId = PrintJob::orderByDesc(
            'id'
        )->value('order_id');

        $nextOrderId = $lastOrderId
            ? ((int) $lastOrderId + 1)
            : 101;

        return [
            'order_id' => $nextOrderId,

            'customer' => 'Harry',

            'invoice_date' =>
                now()->format('d M Y'),

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