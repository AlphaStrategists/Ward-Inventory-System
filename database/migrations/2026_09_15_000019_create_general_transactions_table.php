<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('general_transactions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('item_id');
            $table->integer('ward_id');
            $table->integer('quantity_received')->default(0);
            $table->integer('quantity_issued')->default(0);
            $table->timestamp('date')->useCurrent();
            $table->integer('recorded_by');
            
            $table->foreign('item_id')->references('id')->on('general_items')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('general_transactions');
    }
};