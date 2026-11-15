<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Additive extensions to orders and order_details to support
     * Injection and Injection Antibiotics requisition audit requirements.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_by')->nullable()->after('approved_by');
            $table->timestamp('issued_date')->nullable()->after('issued_by');
            $table->unsignedBigInteger('received_by')->nullable()->after('issued_date');
            $table->timestamp('received_date')->nullable()->after('received_by');

            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('qty_received')->nullable()->after('qty_issued');
            $table->integer('balance_before_order')->nullable()->after('qty_received');
        });
    }

    /**
     * Reverse the migrations.
     * Safely rolls back ONLY the additive columns and foreign keys introduced above.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['balance_before_order', 'qty_received']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['received_date', 'received_by', 'issued_date', 'issued_by']);
        });
    }
};
