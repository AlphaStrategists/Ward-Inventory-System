<?php

namespace App\Observers;

use App\Models\StockReceipt;
use App\Models\StockLedger;

class StockReceiptObserver
{
    /**
     * Handle the StockReceipt "created" event to synchronize with stock_ledger.
     * Replaces the crash-prone MySQL trigger trg_receipt_ledger.
     */
    public function created(StockReceipt $stockReceipt): void
    {
        if ($stockReceipt->quantity_received <= 0) {
            return;
        }

        StockLedger::firstOrCreate(
            [
                'source_table' => 'stock_receipts',
                'source_id' => $stockReceipt->id,
            ],
            [
                'batch_id' => $stockReceipt->batch_id,
                'ward_id' => $stockReceipt->ward_id,
                'transaction_type' => 'RECEIPT',
                'quantity' => $stockReceipt->quantity_received,
                'recorded_by' => $stockReceipt->received_by,
                'created_at' => $stockReceipt->date ?? now(),
            ]
        );
    }
}
