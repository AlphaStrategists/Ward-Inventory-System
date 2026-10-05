<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medicines', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('item_code', 50)->unique();
            $table->string('name', 150);
            $table->integer('category_id');
            $table->integer('unit_id');
            $table->integer('form_id');
            $table->string('strength', 50)->nullable();
            $table->boolean('is_controlled')->default(0);
            $table->integer('min_level')->default(10);
            $table->integer('warning_limit')->default(20);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('form_id')->references('id')->on('medicine_forms')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('medicines');
    }
};