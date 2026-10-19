<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('batch_id');
            $table->integer('ward_id');
            $table->enum('transaction_type', ['RECEIPT', 'DISPENSATION', 'ADJUSTMENT']);
            $table->integer('quantity');
            $table->string('source_table', 50);
            $table->integer('source_id');
            $table->integer('recorded_by');
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['source_table', 'source_id'], 'uk_ledger_source');
            $table->index('batch_id', 'idx_ledger_batch');
            $table->index(['batch_id', 'created_at'], 'idx_ledger_batch_date');
            
            $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });
        
        DB::statement("ALTER TABLE stock_ledger ADD CONSTRAINT chk_ledger_sign CHECK (
            (transaction_type = 'RECEIPT' AND quantity > 0) OR 
            (transaction_type = 'DISPENSATION' AND quantity < 0) OR 
            (transaction_type = 'ADJUSTMENT')
        )");
    }
    public function down(): void {
        Schema::dropIfExists('stock_ledger');
    }
};