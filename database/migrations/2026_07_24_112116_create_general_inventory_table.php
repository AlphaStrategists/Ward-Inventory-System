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
        Schema::create('general_inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->string('item_name', 100);
            $table->string('item_code', 30)->nullable();
            $table->date('entry_date');
            $table->integer('received')->default(0);
            $table->integer('issued')->default(0);
            $table->integer('balance')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_inventory');
    }
};
