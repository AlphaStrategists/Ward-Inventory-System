<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('batch_id');
            $table->integer('ward_id');
            $table->integer('quantity_received');
            $table->integer('received_by');
            $table->timestamp('date')->useCurrent();
            
            $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('restrict');
        });
        
        DB::statement('ALTER TABLE stock_receipts ADD CONSTRAINT chk_qty_received CHECK (quantity_received > 0)');
    }
    public function down(): void {
        Schema::dropIfExists('stock_receipts');
    }
};