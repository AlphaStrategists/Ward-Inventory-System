<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('patients')) {
            if (Schema::hasColumn('patients', 'patient_name')) {
                return; // already the Ward shape
            }
            // C2's legacy table (patient_id, name, nic, bht) has a different shape; keep its data under a new name.
            Schema::rename('patients', 'patients_legacy');
        }

        Schema::create('patients', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('patient_name', 150);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('patients');
    }
};