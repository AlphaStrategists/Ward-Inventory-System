<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wards', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('ward_number', 20)->unique();
            $table->string('ward_name', 100);
        });
    }
    public function down(): void {
        Schema::dropIfExists('wards');
    }
};