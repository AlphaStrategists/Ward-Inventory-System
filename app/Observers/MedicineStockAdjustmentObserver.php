<?php

namespace App\Observers;

use App\Models\MedicineStockAdjustment;
use App\Models\StockLedger;

class MedicineStockAdjustmentObserver
{
    /**
     * Handle the MedicineStockAdjustment "created" event to synchronize with stock_ledger.
     * Replaces the crash-prone MySQL trigger trg_adjustment_ledger.
     */
    public function created(MedicineStockAdjustment $adjustment): void
    {
        StockLedger::firstOrCreate(
            [
                'source_table' => 'medicine_stock_adjustments',
                'source_id' => $adjustment->id,
            ],
            [
                'batch_id' => $adjustment->batch_id,
                'ward_id' => $adjustment->ward_id,
                'transaction_type' => 'ADJUSTMENT',
                'quantity' => $adjustment->quantity,
                'recorded_by' => $adjustment->adjusted_by,
                'created_at' => $adjustment->created_at ?? now(),
            ]
        );
    }
}
