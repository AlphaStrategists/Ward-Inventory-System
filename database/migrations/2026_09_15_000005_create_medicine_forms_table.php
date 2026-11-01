<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // The C2 project's core migration already creates `medicine_forms`.
        if (Schema::hasTable('medicine_forms')) {
            return;
        }

        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('form_name', 50)->unique();
        });
    }
    public function down(): void {
        Schema::dropIfExists('medicine_forms');
    }
};