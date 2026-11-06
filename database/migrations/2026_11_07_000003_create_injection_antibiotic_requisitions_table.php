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
        Schema::create('injection_antibiotic_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('injection_medicines')->cascadeOnDelete();
            $table->date('date');
            $table->integer('balance');
            $table->integer('required_quantity');
            $table->string('request_signature', 150);
            $table->integer('received_quantity')->nullable();
            $table->string('received_signature', 150)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medicine_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('injection_antibiotic_requisitions');
    }
};
