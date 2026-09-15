<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ward_id');
            $table->string('req_no', 50)->unique();
            $table->timestamp('date')->useCurrent();
            $table->integer('requested_by');
            $table->enum('ms_approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->integer('approved_by')->nullable();
            $table->text('remark')->nullable();
            
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::dropIfExists('orders');
    }
};