<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Both projects create tables with the same names (roles, medicines, stock_ledger ...).
 * C2's versions are created first, so this adds the columns the Ward code expects
 * and C2's tables did not have. Every step is guarded, so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'roles', 'suppliers', 'medicine_batches', 'medicines'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'created_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->timestamp('created_at')->nullable()->useCurrent();
                });
            }
        }

        if (Schema::hasTable('stock_ledger')) {
            Schema::table('stock_ledger', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_ledger', 'source_table')) {
                    $table->string('source_table', 50)->nullable();
                }
                if (!Schema::hasColumn('stock_ledger', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                if (!Schema::hasColumn('stock_ledger', 'recorded_by')) {
                    $table->unsignedBigInteger('recorded_by')->nullable();
                }
                if (!Schema::hasColumn('stock_ledger', 'created_at')) {
                    $table->timestamp('created_at')->nullable()->useCurrent();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty: these columns may hold data written by the Ward code.
    }
};
