<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dispensing_reports', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('report_title', 150);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('generated_by');
            $table->string('file_path', 255);
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('dispensing_reports');
    }
};