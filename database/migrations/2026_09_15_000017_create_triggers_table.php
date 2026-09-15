<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::unprepared("
            CREATE TRIGGER trg_receipt_ledger
            AFTER INSERT ON stock_receipts
            FOR EACH ROW
            BEGIN
                INSERT INTO stock_ledger 
                    (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
                VALUES 
                    (NEW.batch_id, NEW.ward_id, 'RECEIPT', NEW.quantity_received, 'stock_receipts', NEW.id, NEW.received_by);
            END;
        ");
        
        DB::unprepared("
            CREATE TRIGGER trg_dispensation_ledger
            AFTER INSERT ON dispensations
            FOR EACH ROW
            BEGIN
                DECLARE v_ward_id INT;
                SELECT ward_id INTO v_ward_id FROM admissions WHERE id = NEW.admission_id;

                INSERT INTO stock_ledger 
                    (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
                VALUES 
                    (NEW.batch_id, v_ward_id, 'DISPENSATION', -NEW.qty_given, 'dispensations', NEW.id, NEW.issued_by);
            END;
        ");
        
        DB::unprepared("
            CREATE TRIGGER trg_adjustment_ledger
            AFTER INSERT ON medicine_stock_adjustments
            FOR EACH ROW
            BEGIN
                INSERT INTO stock_ledger 
                    (batch_id, ward_id, transaction_type, quantity, source_table, source_id, recorded_by)
                VALUES 
                    (NEW.batch_id, NEW.ward_id, 'ADJUSTMENT', NEW.quantity, 'medicine_stock_adjustments', NEW.id, NEW.adjusted_by);
            END;
        ");
    }
    public function down(): void {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_receipt_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_dispensation_ledger');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_adjustment_ledger');
    }
};