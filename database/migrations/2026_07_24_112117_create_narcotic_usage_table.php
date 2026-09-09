<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('narcotic_usage', function (Blueprint $table) {
            $table->id('usage_id');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id');
            $table->string('bed_no', 20)->nullable();
            $table->date('usage_date');
            $table->time('usage_time');
            $table->string('dosage', 50);
            $table->foreignId('recorded_by_staff_id')->nullable()->constrained('staff', 'staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('narcotic_usage');
    }
};
