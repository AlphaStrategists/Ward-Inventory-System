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
        Schema::create('surgical_consumable_1s', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('name of first aid supply')->nullable();
            $table->integer('balance')->nullable();
            $table->integer('requested quantity')->nullable();
            $table->string('confirmation signature(requested)')->nullable();
            $table->integer('received quantity')->nullable();
            $table->string('confirmation signature(received)')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_consumable_1s');
    }
};
