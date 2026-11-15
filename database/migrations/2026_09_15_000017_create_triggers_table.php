<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Triggers have been deprecated and replaced with robust Eloquent Model Observers
        // (StockReceiptObserver, DispensationObserver, MedicineStockAdjustmentObserver)
        // to prevent fatal MySQL / MariaDB server crashes (Access Violation 0xc0000005) in XAMPP.
        DB::unprepared('DROP TRIGGER IF EXISTS trg_receipt_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_dispensation_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_adjustment_ledger');
    }
    public function down(): void {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_receipt_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_dispensation_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_adjustment_ledger');
    }
};