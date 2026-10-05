<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('dispensations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('admission_id');
            $table->integer('batch_id');
            $table->timestamp('date')->useCurrent();
            $table->integer('qty_given');
            $table->string('dosage', 100);
            $table->string('usage_time', 100)->nullable();
            $table->integer('issued_by');
            $table->integer('witnessed_by')->nullable();
            
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('restrict');
            $table->foreign('batch_id')->references('id')->on('medicine_batches')->onDelete('restrict');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('witnessed_by')->references('id')->on('users')->onDelete('restrict');
        });
        
        DB::statement('ALTER TABLE dispensations ADD CONSTRAINT chk_qty_given CHECK (qty_given > 0)');
    }
    public function down(): void {
        Schema::dropIfExists('dispensations');
    }
};