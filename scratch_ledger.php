<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$selectedMedicine = App\Models\Medicine::first();
echo "Medicine: " . $selectedMedicine->id . "\n";

$dispensations = App\Models\Dispensation::with(['admission.patient', 'issuedBy', 'batch'])
    ->whereHas('batch', function($q) use ($selectedMedicine) {
        $q->where('medicine_id', $selectedMedicine->id);
    })
    ->get()
    ->map(function ($item) {
        // Here we combine date and time!
        $dateTimeStr = $item->date;
        if ($item->usage_time) {
            $dateOnly = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            $dateTimeStr = $dateOnly . ' ' . $item->usage_time;
        }
        return (object) [
            'type' => 'dispensation',
            'timestamp' => \Carbon\Carbon::parse($dateTimeStr)->timestamp,
            'model' => $item,
            'qty' => -($item->qty_given)
        ];
    });

$receipts = App\Models\StockReceipt::whereHas('batch', function($q) use ($selectedMedicine) {
        $q->where('medicine_id', $selectedMedicine->id);
    })
    ->get()
    ->map(function ($item) {
        return (object) [
            'type' => 'receipt',
            'timestamp' => \Carbon\Carbon::parse($item->date)->timestamp,
            'model' => $item,
            'qty' => $item->quantity_received
        ];
    });

$adjustments = App\Models\MedicineStockAdjustment::whereHas('batch', function($q) use ($selectedMedicine) {
        $q->where('medicine_id', $selectedMedicine->id);
    })
    ->get()
    ->map(function ($item) {
        return (object) [
            'type' => 'adjustment',
            'timestamp' => \Carbon\Carbon::parse($item->created_at)->timestamp,
            'model' => $item,
            'qty' => $item->quantity
        ];
    });

$ledger = $dispensations->concat($receipts)->concat($adjustments)->sortBy('timestamp')->values();

$running_balance = 0;
echo "Ledger:\n";
foreach ($ledger as $entry) {
    $running_balance += $entry->qty;
    echo $entry->type . " at " . \Carbon\Carbon::createFromTimestamp($entry->timestamp)->toDateTimeString() . " (" . $entry->qty . ") -> " . $running_balance . "\n";
}
