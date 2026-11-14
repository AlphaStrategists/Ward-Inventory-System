<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (Schema::hasColumn('medicines', 'strength')) {
                $table->dropColumn('strength');
            }

            if (!Schema::hasColumn('medicines', 'strength_value')) {
                $table->decimal('strength_value', 10, 2)->nullable()->after('form_id');
            }

            if (!Schema::hasColumn('medicines', 'strength_unit_id')) {
                $table->integer('strength_unit_id')->nullable()->after('strength_value');
                $table->foreign('strength_unit_id')
                    ->references('id')
                    ->on('strength_units')
                    ->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            if (Schema::hasColumn('medicines', 'strength_unit_id')) {
                $table->dropForeign(['strength_unit_id']);
                $table->dropColumn('strength_unit_id');
            }

            if (Schema::hasColumn('medicines', 'strength_value')) {
                $table->dropColumn('strength_value');
            }

            if (!Schema::hasColumn('medicines', 'strength')) {
                $table->string('strength', 50)->nullable()->after('form_id');
            }
        });
    }
};

