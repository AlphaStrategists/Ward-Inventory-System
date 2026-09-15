<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('general_stock_adjustments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('item_id');
            $table->integer('ward_id');
            $table->enum('adjustment_type', ['DAMAGED', 'LOST', 'COUNT_CORRECTION', 'RETURN']);
            $table->integer('quantity');
            $table->text('reason');
            $table->integer('adjusted_by');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('item_id')->references('id')->on('general_items')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('general_stock_adjustments');
    }
};