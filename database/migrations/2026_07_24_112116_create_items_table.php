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
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_name', 100);
            $table->enum('item_type', ['medicine', 'surgical', 'injection']);
            $table->enum('item_subtype', [
                'narcotic', 'syrup', 'iv_fluid', 'oral_countable',
                'oral_antibiotic', 'bulk_medicine', 'surgical_consumable_1',
                'surgical_consumable_2', 'local_purchase', 'injection',
                'injection_antibiotic',
            ]);
            $table->enum('workflow_pattern', [
                'request_simple', 'request_approved', 'narcotic_direct', 'consumable_direct',
            ]);
            $table->integer('quantity')->default(0);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
