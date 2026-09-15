<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admissions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('patient_id');
            $table->string('bht_no', 50)->unique();
            $table->integer('ward_id');
            $table->timestamp('admit_date')->useCurrent();
            $table->enum('status', ['Admitted', 'Discharged', 'Transferred'])->default('Admitted');
            
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
        });
    }
    public function down(): void {
        Schema::dropIfExists('admissions');
    }
};