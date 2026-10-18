<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Core tables used by the Medicine / Order based ward inventory pages
     * (injections, injection antibiotics and the MS approval portal).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name', 50)->unique();
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->string('ward_number', 20)->unique();
            $table->string('ward_name', 100);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_name', 50)->unique();
        });

        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->string('form_name', 50)->unique();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name', 100);
            $table->string('contact_info')->nullable();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50)->unique();
            $table->string('name', 150);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('form_id')->constrained('medicine_forms');
            $table->string('strength', 50)->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->unsignedInteger('min_level')->default(0);
            $table->unsignedInteger('warning_limit')->default(0);
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('batch_no', 100);
            $table->date('expiry_date')->nullable();
        });

        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('medicine_batches')->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained('wards');
            $table->unsignedInteger('quantity_received');
            $table->foreignId('received_by')->constrained('users');
            $table->dateTime('date');
        });

        // Signed quantities: receipts are positive, issues / wastage are negative.
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('medicine_batches')->cascadeOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->integer('quantity');
            $table->string('transaction_type', 30)->default('receipt');
            $table->dateTime('transaction_date')->useCurrent();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards');
            $table->string('req_no', 50)->unique();
            $table->dateTime('date');
            $table->foreignId('requested_by')->constrained('users');
            $table->string('ms_approval_status', 20)->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remark')->nullable();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // Restrict: a medicine that has requisition history must not be silently deleted.
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedInteger('qty_requested');
            $table->unsignedInteger('qty_issued')->default(0);
            $table->text('remark')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('stock_ledger');
        Schema::dropIfExists('stock_receipts');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('medicine_forms');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('roles');
    }
};
