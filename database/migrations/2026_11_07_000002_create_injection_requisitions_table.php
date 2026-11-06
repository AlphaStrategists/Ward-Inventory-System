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
        Schema::create('injection_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('injection_medicines')->cascadeOnDelete();
            $table->date('date');
            $table->integer('requested_quantity');
            $table->string('request_signature', 150);
            $table->enum('ms_approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('ms_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ms_approved_at')->nullable();
            $table->integer('received_quantity')->nullable();
            $table->string('issued_officer_signature', 150)->nullable();
            $table->date('issued_officer_date')->nullable();
            $table->string('received_signature', 150)->nullable();
            $table->date('received_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medicine_id', 'date']);
            $table->index('ms_approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('injection_requisitions');
    }
};
