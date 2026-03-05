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