<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer',
        'total',
        'printer_id',
        'printer_name',
        'status',
        'file_name',
        'file_path',
        'error_message',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'printed_at' => 'datetime',
        ];
    }
}