<?php

namespace App\Console\Commands;

use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockReceipt;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Console\Command;

class ProcessExpiredMedicines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'narcotics:expire-batches';

    /**
     * The console command alternative signatures/aliases.
     *
     * @var array
     */
    protected $aliases = [
        'medicines:expire-batches',
        'medicines:process-expired',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all expired medicine batches with positive stock, record StockAdjustment deductions, and update batch quantities to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        $now = now();
        $this->info("Checking for expired batches as of {$today}...");

        $batches = MedicineBatch::where('expiry_date', '<', $today)
            ->orWhere('expiry_date', '<', $now)
            ->get();

        $count = 0;
        $totalDeducted = 0;
        $systemUserId = User::value('id') ?? 1;
        $defaultWardId = Ward::value('id') ?? 1;

        foreach ($batches as $batch) {
            $batchQty = (int) $batch->current_balance;

            if ($batchQty > 0) {
                // Determine ward balance(s) for this batch from stock_ledger
                $wardBalances = StockLedger::where('batch_id', $batch->id)
                    ->groupBy('ward_id')
                    ->selectRaw('ward_id, SUM(quantity) as balance')
                    ->having('balance', '>', 0)
                    ->get();

                if ($wardBalances->isEmpty()) {
                    $receiptWard = StockReceipt::where('batch_id', $batch->id)->value('ward_id');
                    $wardBalances = collect([(object)[
                        'ward_id' => $receiptWard ?: $defaultWardId,
                        'balance' => $batchQty
                    ]]);
                }

                foreach ($wardBalances as $wb) {
                    $qty = (int) $wb->balance;
                    if ($qty <= 0) {
                        continue;
                    }

                    // Create StockAdjustment record (type: deduction, reason: Expired)
                    StockAdjustment::create([
                        'batch_id' => $batch->id,
                        'ward_id' => $wb->ward_id,
                        'adjustment_type' => 'EXPIRY',
                        'type' => 'deduction',
                        'quantity' => -$qty,
                        'reason' => 'Expired',
                        'adjusted_by' => $systemUserId,
                        'created_at' => now(),
                    ]);

                    $totalDeducted += $qty;
                }

                // Update batch status / quantity
                $batch->status = 'expired';
                $batch->quantity = 0;
                $batch->refresh();

                $this->line("Deducted {$batchQty} units from expired Batch {$batch->batch_no} (ID: {$batch->id}).");
                $count++;
            }
        }

        $this->info("Successfully processed {$count} expired batches (Total units deducted: {$totalDeducted}).");
        return 0;
    }
}
