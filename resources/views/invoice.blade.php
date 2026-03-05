<!DOCTYPE html>
<html>
<head>
<title>Invoice</title>

<style>

body{
font-family: Arial;
}

table{
width:100%;
border-collapse: collapse;
}

td,th{
border:1px solid black;
padding:8px;
}

</style>

</head>

<body>

<h2>Invoice</h2>

<p><b>Order ID:</b> {{ $order_id }}</p>
<p><b>Customer:</b> {{ $customer }}</p>

<table>

<tr>
<th>Item</th>
<th>Price</th>
</tr>

<tr>
<td>Product 1</td>
<td>1000</td>
</tr>

<tr>
<td>Product 2</td>
<td>500</td>
</tr>

<tr>
<th>Total</th>
<th>{{ $total }}</th>
</tr>

</table>

</body>
</html>