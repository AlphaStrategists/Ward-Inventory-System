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
        if (!Schema::hasColumn('orders', 'approved_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'approved_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('approved_at');
            });
        }
    }
};
