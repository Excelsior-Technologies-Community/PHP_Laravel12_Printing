<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();

            $table->string('order_id');
            $table->string('customer');
            $table->decimal('total', 10, 2);

            $table->string('printer_id')->nullable();
            $table->string('printer_name')->nullable();

            $table->string('status')->default('pending');

            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('printed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};