<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('medicine_id');
            $table->integer('supplier_id')->nullable();
            $table->string('batch_no', 100);
            $table->date('expiry_date');
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['medicine_id', 'batch_no'], 'uk_medicine_batch');
            $table->index(['medicine_id', 'expiry_date'], 'idx_medicine_expiry');
            
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::dropIfExists('medicine_batches');
    }
};