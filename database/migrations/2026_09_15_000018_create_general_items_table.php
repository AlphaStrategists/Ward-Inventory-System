<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('general_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('item_code', 50)->unique();
            $table->string('name', 150);
            $table->integer('category_id');
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('general_items');
    }
};