<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Invoice Preview</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f6fa;
        }

        .invoice-container {
            max-width: 850px;
            margin: 40px auto;
        }

        .invoice-paper {
            background: white;
            padding: 45px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 700;
        }

        .total-box {
            font-size: 20px;
            font-weight: 700;
        }
    </style>

</head>

<body>

<div class="invoice-container">

    <div class="d-flex justify-content-between mb-4">

        <a href="{{ route('printing.dashboard') }}"
           class="btn btn-secondary">
            ← Dashboard
        </a>

        <div>

            <a href="{{ route('invoice.download') }}"
               class="btn btn-success">
                Download PDF
            </a>

        </div>

    </div>

    <div class="invoice-paper">

        <div class="d-flex justify-content-between">

            <div>
                <div class="invoice-title">
                    INVOICE
                </div>

                <p class="text-muted">
                    Printing Management System
                </p>
            </div>

            <div class="text-end">

                <strong>
                    Order #{{ $order_id }}
                </strong>

                <br>

                <span>
                    {{ $invoice_date }}
                </span>

            </div>

        </div>

        <hr>

        <div class="mb-4">

            <strong>Customer:</strong>

            {{ $customer }}

        </div>

        <table class="table table-bordered">

            <thead class="table-light">

                <tr>

                    <th>Item</th>

                    <th width="120">
                        Quantity
                    </th>

                    <th width="150">
                        Price
                    </th>

                    <th width="150">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($items as $item)

                    <tr>

                        <td>
                            {{ $item['name'] }}
                        </td>

                        <td>
                            {{ $item['quantity'] }}
                        </td>

                        <td>
                            ₹{{ number_format(
                                $item['price'],
                                2
                            ) }}
                        </td>

                        <td>
                            ₹{{ number_format(
                                $item['quantity'] *
                                $item['price'],
                                2
                            ) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

            <tfoot>

                <tr>

                    <td colspan="3"
                        class="text-end total-box">

                        Grand Total

                    </td>

                    <td class="total-box">

                        ₹{{ number_format($total, 2) }}

                    </td>

                </tr>

            </tfoot>

        </table>

        <div class="alert alert-info mt-4">

            <strong>Printing workflow:</strong>

            Preview → Select Printer →
            Print → Print Job History

        </div>

    </div>

</div>

</body>

</html>