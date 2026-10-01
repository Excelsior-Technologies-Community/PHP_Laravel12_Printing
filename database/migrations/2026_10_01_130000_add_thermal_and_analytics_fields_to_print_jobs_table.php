<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->string('format_type', 50)->default('invoice_pdf')->after('status');
            $table->integer('pages_count')->default(1)->after('format_type');
            $table->decimal('paper_cost', 8, 4)->default(0.0500)->after('pages_count');
            $table->string('failover_printer')->nullable()->after('printer_name');
            $table->boolean('is_failover')->default(false)->after('failover_printer');
        });
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropColumn(['format_type', 'pages_count', 'paper_cost', 'failover_printer', 'is_failover']);
        });
    }
};
