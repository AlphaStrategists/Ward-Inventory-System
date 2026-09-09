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
        Schema::create('requested_stock', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->foreignId('requested_by_staff_id')->constrained('staff', 'staff_id');
            $table->date('request_date');
            $table->integer('required_quantity');
            $table->integer('balance_before')->nullable();
            $table->enum('status', ['pending', 'approved', 'issued', 'low_stock', 'completed'])
                ->default('pending');

            $table->foreignId('confirm_request_staff_id')->nullable()->constrained('staff', 'staff_id');
            $table->foreignId('approved_by_ms_staff_id')->nullable()->constrained('staff', 'staff_id');

            $table->integer('received_quantity')->nullable();
            $table->date('received_date')->nullable();
            $table->foreignId('confirm_received_staff_id')->nullable()->constrained('staff', 'staff_id');

            $table->foreignId('issued_officer_staff_id')->nullable()->constrained('staff', 'staff_id');
            $table->date('issued_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requested_stock');
    }
};
