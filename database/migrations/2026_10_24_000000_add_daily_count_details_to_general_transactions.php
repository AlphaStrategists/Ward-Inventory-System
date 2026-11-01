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
        Schema::table('general_transactions', function (Blueprint $table) {
            $table->string('sisters_initials', 50)->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'sisters_initials',
                'remarks',
            ]);
        });
    }
};