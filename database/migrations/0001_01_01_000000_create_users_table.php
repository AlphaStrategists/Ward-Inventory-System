<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_transactions', function (Blueprint $table) {
            $table->string('sisters_initials', 20)->nullable()->after('recorded_by');
            $table->text('remarks')->nullable()->after('sisters_initials');
        });
    }

    public function down(): void
    {
        Schema::table('general_transactions', function (Blueprint $table) {
            $table->dropColumn(['sisters_initials', 'remarks']);
        });
    }
};
