<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('units', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('unit_name', 50)->unique();
        });
    }
    public function down(): void {
        Schema::dropIfExists('units');
    }
};