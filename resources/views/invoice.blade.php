<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Invoice #{{ $order_id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #222;
        }

        .invoice-header {
            width: 100%;
            margin-bottom: 25px;
        }

        .invoice-header td {
            border: none;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
        }

        .details {
            margin-bottom: 20px;
        }

        .details td {
            border: none;
            padding: 4px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 9px;
        }

        table.items th {
            background: #eeeeee;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>

<body>

<table class="invoice-header">
    <tr>
        <td>
            <div class="title">INVOICE</div>
        </td>

        <td class="text-right">
            <strong>Order #{{ $order_id }}</strong>
            <br>
            Date: {{ $invoice_date }}
        </td>
    </tr>
</table>

<table class="details">
    <tr>
        <td>
            <strong>Customer:</strong>
            {{ $customer }}
        </td>
    </tr>
</table>

<table class="items">

    <thead>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
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

                <td class="text-right">
                    &#8377;{{ number_format($item['price'], 2) }}
                </td>

                <td class="text-right">
                    &#8377;{{ number_format(
                        $item['quantity'] * $item['price'],
                        2
                    ) }}
                </td>
            </tr>

        @endforeach

        <tr class="total-row">
            <td colspan="3" class="text-right">
                Grand Total
            </td>

            <td class="text-right">
                &#8377;{{ number_format($total, 2) }}
            </td>
        </tr>

    </tbody>

</table>

<div class="footer">
    Thank you for your business!
</div>

</body>
</html>