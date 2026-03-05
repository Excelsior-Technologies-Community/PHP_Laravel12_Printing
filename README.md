#  PHP_Laravel12_Printing

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![Printing](https://img.shields.io/badge/Printing-PrintNode-green)

---

# Overview

This project demonstrates **server-side printing in Laravel 12** using the **PrintNode API**.
The application generates a **PDF invoice using DomPDF** and sends it directly to a printer connected through the **PrintNode client**.

This setup is commonly used in:

* E-commerce order invoices
* POS billing systems
* Restaurant billing
* Warehouse label printing

---

# Features

* Generate invoice PDF dynamically
* Send print jobs directly from Laravel server
* Integrate with PrintNode API
* Print automatically through connected printers
* Simple and lightweight Laravel implementation

---

#  Folder Structure

```
app
 └── Http
      └── Controllers
           └── PrintController.php

config
 └── printing.php

resources
 └── views
      └── invoice.blade.php

routes
 └── web.php
```

---

# Printing Workflow

```
Laravel Application
        ↓
Generate PDF Invoice (DomPDF)
        ↓
Send Print Job (PrintNode API)
        ↓
PrintNode Client
        ↓
Printer
```

---

# Requirements

Before starting, make sure you have:

* PHP 8.1+
* Composer
* Laravel 12
* PrintNode Account
* PrintNode Desktop Client Installed
* Printer connected to your system

---

# Step 1 — Create Laravel Project

Install Laravel using Composer.

```
composer create-project laravel/laravel printnode-demo
```

Start the Laravel development server.

```
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000
```

---

# Database Configuration (.env)

Update database credentials inside `.env`.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
```

---

# Step 2 — Install Printing Package

Install the Laravel printing package.

```
composer require rawilk/laravel-printing
```

Publish the configuration file.

```
php artisan vendor:publish --tag=printing-config
```

This will create:

```
config/printing.php
```

---

# Step 3 — Install PDF Generator

Install DomPDF for generating invoices.

```
composer require barryvdh/laravel-dompdf
```

---

# Step 4 — Create PrintNode Account

1. Go to

```
https://app.printnode.com
```

2. Create an account

   <img width="786" height="448" alt="Screenshot 2026-03-05 172218" src="https://github.com/user-attachments/assets/bfb2f943-321c-4e0a-bbc8-1eb7f9610f63" />

---
3. Verify your email
4. Install **PrintNode Client**

   <img width="1521" height="415" alt="Screenshot 2026-03-05 180038" src="https://github.com/user-attachments/assets/8e5db607-4a96-4028-a8cc-0ab8ac57d9c1" />

---
5.Connect your printer inside the PrintNode client.

   <img width="596" height="430" alt="Screenshot 2026-03-05 172205" src="https://github.com/user-attachments/assets/360dd5c1-e902-4b1e-80ed-88022afdedc5" />

---
6.Generate an **API Key** from the dashboard.

   <img width="703" height="330" alt="Screenshot 2026-03-05 165555" src="https://github.com/user-attachments/assets/145f10a3-5bfb-4b0a-aff9-3cd2995ced11" />

---

# Step 5 — Configure Environment Variables

Open `.env` and add:

```
PRINTING_DRIVER=printnode
PRINT_NODE_API_KEY=YOUR_API_KEY_HERE
```


# Step 6 — Configure Printing Driver

Open:

```
config/printing.php
```

```
<?php

declare(strict_types=1);

use Rawilk\Printing\Enums\PrintDriver;

return [

'driver' => env('PRINTING_DRIVER', PrintDriver::PrintNode->value),

'drivers' => [

PrintDriver::PrintNode->value => [
    'key' => env('PRINT_NODE_API_KEY'),
],

PrintDriver::Cups->value => [
    'ip' => env('CUPS_SERVER_IP'),
    'username' => env('CUPS_SERVER_USERNAME'),
    'password' => env('CUPS_SERVER_PASSWORD'),
    'port' => (int) env('CUPS_SERVER_PORT'),
    'secure' => env('CUPS_SERVER_SECURE'),
],

],

'default_printer_id' => 75236937,

];
```

---

# Step 7 — Clear Laravel Cache

Run the following commands.

```
php artisan optimize:clear

php artisan config:clear

php artisan cache:clear

php artisan config:cache
```

---

# Step 8 — Create Controller

Generate a controller.

```
php artisan make:controller PrintController
```

File:

```
app/Http/Controllers/PrintController.php
```

```
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
```

---

# Step 9 — Create Invoice View

Create a Blade template.

```
resources/views/invoice.blade.php
```

```
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
```

---

# Step 10 — Create Route

```
routes/web.php
```

```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintController;

// Route to generate the invoice PDF and send it to the printer
Route::get('/print', [PrintController::class, 'printInvoice']);
```

---

# Step 11 — Test Printer Connection

Add a temporary route.

```
use Rawilk\Printing\Facades\Printing;

// Route to retrieve and display the list of available printers from PrintNode
Route::get('/printers', function () {
    return Printing::printers();
});
```

Open:

```
http://127.0.0.1:8000/printers
```
Expected response:

<img width="341" height="101" alt="Screenshot 2026-03-05 182744" src="https://github.com/user-attachments/assets/00796c2c-4859-43cf-98b8-bdc393385a7d" />


---

# Step 12 — Test Printing

Open:

```
http://127.0.0.1:8000/print
```

If everything is configured correctly:

* Laravel generates a PDF invoice
* The print job is sent to PrintNode
* PrintNode client forwards the job to the printer

The route returns:

```
Invoice sent to printer!
```
<img width="352" height="85" alt="Screenshot 2026-03-05 171906" src="https://github.com/user-attachments/assets/abb45dac-51f1-47df-942b-5774e9404706" />

---

## Save Print Output Dialog

Since this project uses **Microsoft Print to PDF**, Windows displays a **Save Print Output As** dialog.

You can:

* Select folder location
* Enter file name
* Save the generated invoice as a PDF

Example:

```
File Name: ABC.pdf
Save as type: PDF Document (*.pdf)
```

After clicking **Save**, the invoice will be generated as a PDF file.

<img width="945" height="530" alt="Screenshot 2026-03-05 171924" src="https://github.com/user-attachments/assets/3774f0cc-cf4d-45c7-bc8e-678b7c1243eb" />

---

## Generated Invoice Output

The PDF will contain:

<img width="815" height="454" alt="Screenshot 2026-03-05 172005" src="https://github.com/user-attachments/assets/1e4cbde1-cdf8-4c88-8403-328b9ded4ce8" />

---

## Print Node History

<img width="1906" height="948" alt="Screenshot 2026-03-05 172122" src="https://github.com/user-attachments/assets/1dd48214-9424-4b4f-a909-49a1d9586803" />


## Troubleshooting

### API Key Not Found (401)

Make sure:

* API key is correct
* Email is verified in PrintNode
* Cache is cleared

```
php artisan optimize:clear
```

---

### Printers Not Showing

Ensure:

* PrintNode Client is running
* Printer status is **Online**
* Correct PrintNode account is logged in

---

## Conclusion

You have successfully integrated **server-side printing in Laravel using PrintNode**.

The application can now:

* Generate invoices dynamically
* Send print jobs directly from the server
* Print automatically through connected printers

This setup is widely used in:

* E-commerce systems
* POS billing software
* Restaurant billing
* Warehouse printing systems

---


