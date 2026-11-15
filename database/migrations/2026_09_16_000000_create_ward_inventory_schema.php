<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations matching the existing ward_48_inventory schema.
     */
    public function up(): void
    {
        // 1. Roles & Permissions
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('role_name', 50)->unique();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('permission_name', 100)->unique();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('permission_id');
                $table->timestamp('created_at')->useCurrent();
                $table->primary(['role_id', 'permission_id']);
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            });
        }

        // 2. Master Tables
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->text('description')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->increments('id');
                $table->string('unit_name', 50)->unique();
            });
        }

        if (!Schema::hasTable('medicine_forms')) {
            Schema::create('medicine_forms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('form_name', 50)->unique();
            });
        }

        if (!Schema::hasTable('wards')) {
            Schema::create('wards', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ward_number', 20)->unique();
                $table->string('ward_name', 100);
            });
        }

        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('supplier_name', 150);
                $table->string('contact_info', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. Users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('email', 150)->unique();
                $table->string('password', 255);
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('ward_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('set null');
            });
        }

        // 4. Patients & Admissions
        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('patient_name', 150);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('admissions')) {
            Schema::create('admissions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('patient_id');
                $table->string('bht_no', 50)->unique();
                $table->unsignedInteger('ward_id');
                $table->timestamp('admit_date')->useCurrent();
                $table->enum('status', ['Admitted', 'Discharged', 'Transferred'])->default('Admitted');
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            });
        }

        // 5. Medicines & Batches
        if (!Schema::hasTable('medicines')) {
            Schema::create('medicines', function (Blueprint $table) {
                $table->increments('id');
                $table->string('item_code', 50)->unique();
                $table->string('name', 150);
                $table->unsignedInteger('category_id');
                $table->unsignedInteger('unit_id');
                $table->unsignedInteger('form_id');
                $table->string('strength', 50)->nullable();
                $table->tinyInteger('is_controlled')->default(0);
                $table->integer('min_level')->default(10);
                $table->integer('warning_limit')->default(20);
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
                $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
                $table->foreign('form_id')->references('id')->on('medicine_forms')->onDelete('restrict');
            });
        }

        if (!Schema::hasTable('medicine_batches')) {
            Schema::create('medicine_batches', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('medicine_id');
                $table->unsignedInteger('supplier_id')->nullable();
                $table->string('batch_no', 100);
                $table->date('expiry_date');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['medicine_id', 'batch_no'], 'uk_medicine_batch');
                $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('restrict');
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
                $table->index(['medicine_id', 'expiry_date'], 'idx_medicine_expiry');
            });
        }

        // 6. Stock Receipts
        if (!Schema::hasTable('stock_receipts')) {
            Schema::create('stock_receipts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('batch_id');
                $table->unsignedInteger('ward_id');
                $table->integer('quantity_received');
                $table->unsignedInteger('received_by');
                $table->timestamp('date')->useCurrent();
                $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
                $table->foreign('received_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 7. Stock Ledger
        if (!Schema::hasTable('stock_ledger')) {
            Schema::create('stock_ledger', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('batch_id');
                $table->unsignedInteger('ward_id');
                $table->enum('transaction_type', ['RECEIPT', 'DISPENSATION', 'ADJUSTMENT']);
                $table->integer('quantity');
                $table->string('source_table', 50);
                $table->unsignedInteger('source_id');
                $table->unsignedInteger('recorded_by');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['source_table', 'source_id'], 'uk_ledger_source');
                $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
                $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 8. Dispensations
        if (!Schema::hasTable('dispensations')) {
            Schema::create('dispensations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('admission_id');
                $table->unsignedInteger('batch_id');
                $table->timestamp('date')->useCurrent();
                $table->integer('qty_given');
                $table->string('dosage', 100);
                $table->string('usage_time', 100)->nullable();
                $table->unsignedInteger('issued_by');
                $table->unsignedInteger('witnessed_by')->nullable();
                $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('restrict');
                $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
                $table->foreign('issued_by')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('witnessed_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 9. Medicine Adjustments
        if (!Schema::hasTable('medicine_stock_adjustments')) {
            Schema::create('medicine_stock_adjustments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('batch_id');
                $table->unsignedInteger('ward_id');
                $table->enum('adjustment_type', ['EXPIRY', 'DAMAGED', 'LOST', 'COUNT_CORRECTION', 'RETURN']);
                $table->integer('quantity');
                $table->text('reason');
                $table->unsignedInteger('adjusted_by');
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
                $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        // 10. General Items & Support Tables
        if (!Schema::hasTable('general_items')) {
            Schema::create('general_items', function (Blueprint $table) {
                $table->increments('id');
                $table->string('item_code', 50)->unique();
                $table->string('name', 150);
                $table->unsignedInteger('category_id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            });
        }

        if (!Schema::hasTable('general_transactions')) {
            Schema::create('general_transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('ward_id');
                $table->integer('quantity_received')->default(0)->nullable();
                $table->integer('quantity_issued')->default(0)->nullable();
                $table->timestamp('date')->useCurrent();
                $table->unsignedInteger('recorded_by');
                $table->foreign('item_id')->references('id')->on('general_items')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
                $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        if (!Schema::hasTable('general_stock_adjustments')) {
            Schema::create('general_stock_adjustments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('ward_id');
                $table->enum('adjustment_type', ['DAMAGED', 'LOST', 'COUNT_CORRECTION', 'RETURN']);
                $table->integer('quantity');
                $table->text('reason');
                $table->unsignedInteger('adjusted_by');
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('item_id')->references('id')->on('general_items')->onDelete('restrict');
                $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
                $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_stock_adjustments');
        Schema::dropIfExists('general_transactions');
        Schema::dropIfExists('general_items');
        Schema::dropIfExists('medicine_stock_adjustments');
        Schema::dropIfExists('dispensations');
        Schema::dropIfExists('stock_ledger');
        Schema::dropIfExists('stock_receipts');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('medicine_forms');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
