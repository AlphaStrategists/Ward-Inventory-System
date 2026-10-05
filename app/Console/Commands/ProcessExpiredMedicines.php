<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessExpiredMedicines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medicines:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically detect and deduct stock for expired medicine batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $this->info("Checking for expired medicines as of {$today}...");

        $batches = \App\Models\MedicineBatch::where('expiry_date', '<', $today)->get();
        $count = 0;

        foreach ($batches as $batch) {
            $received = \App\Models\StockReceipt::where('batch_id', $batch->id)->sum('quantity_received');
            $dispensed = \App\Models\Dispensation::where('batch_id', $batch->id)->sum('qty_given');
            $adjustments = \App\Models\MedicineStockAdjustment::where('batch_id', $batch->id)->sum('quantity');
            
            $availableQty = $received - $dispensed + $adjustments;

            if ($availableQty > 0) {
                // Determine ward from the first receipt (or default)
                $wardId = \App\Models\StockReceipt::where('batch_id', $batch->id)->value('ward_id') ?? 1;

                \App\Models\MedicineStockAdjustment::create([
                    'batch_id' => $batch->id,
                    'ward_id' => $wardId,
                    'adjustment_type' => 'EXPIRY',
                    'quantity' => -$availableQty,
                    'reason' => 'Auto-adjustment: Batch Expired',
                    'adjusted_by' => 1, // System User ID
                    'created_at' => now(),
                ]);

                $this->line("Deducted {$availableQty} units from expired Batch {$batch->batch_no}");
                $count++;
            }
        }

        $this->info("Processed {$count} expired batches successfully.");
    }
}
