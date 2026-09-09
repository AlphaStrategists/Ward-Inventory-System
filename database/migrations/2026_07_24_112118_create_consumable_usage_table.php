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
        Schema::create('consumable_usage', function (Blueprint $table) {
            $table->id('usage_id');
            $table->foreignId('item_id')->constrained('items', 'item_id');
            $table->string('bed_head_no', 20)->nullable();
            $table->date('usage_date');
            $table->integer('quantity');
            $table->integer('balance');
            $table->foreignId('incharge_staff_id')->constrained('staff', 'staff_id');
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_usage');
    }
};
