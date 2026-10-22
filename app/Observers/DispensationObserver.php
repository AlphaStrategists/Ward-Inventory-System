<?php

namespace App\Observers;

use App\Models\Dispensation;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class DispensationObserver
{
    /**
     * Handle the Dispensation "created" event to synchronize with stock_ledger.
     * Replaces the crash-prone MySQL trigger trg_dispensation_ledger.
     */
    public function created(Dispensation $dispensation): void
    {
        $wardId = $dispensation->admission ? $dispensation->admission->ward_id : null;
        if (!$wardId && $dispensation->admission_id) {
            $wardId = DB::table('admissions')->where('id', $dispensation->admission_id)->value('ward_id');
        }
        if (!$wardId) {
            $wardId = DB::table('wards')->value('id') ?? 1;
        }

        StockLedger::firstOrCreate(
            [
                'source_table' => 'dispensations',
                'source_id' => $dispensation->id,
            ],
            [
                'batch_id' => $dispensation->batch_id,
                'ward_id' => $wardId,
                'transaction_type' => 'DISPENSATION',
                'quantity' => -$dispensation->qty_given,
                'recorded_by' => $dispensation->issued_by,
                'created_at' => $dispensation->date ?? now(),
            ]
        );
    }
}
