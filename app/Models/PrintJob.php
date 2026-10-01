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
        'format_type',
        'pages_count',
        'paper_cost',
        'failover_printer',
        'is_failover',
        'file_name',
        'file_path',
        'error_message',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paper_cost' => 'decimal:4',
            'is_failover' => 'boolean',
            'printed_at' => 'datetime',
        ];
    }
}