<?php
$files = [
    'GeneralStockAdjustment.php' => "    public function adjustedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'adjusted_by');\n    }\n",
    'GeneralTransaction.php' => "    public function recordedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'recorded_by');\n    }\n",
    'StockReceipt.php' => "    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'received_by');\n    }\n",
    'Order.php' => "    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'requested_by');\n    }\n\n    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo(User::class, 'approved_by');\n    }\n",
    'Ward.php' => "    public function stockReceipts(): \Illuminate\Database\Eloquent\Relations\HasMany\n    {\n        return \$this->hasMany(StockReceipt::class, 'ward_id');\n    }\n",
    'MedicineBatch.php' => "    public function receipts(): \Illuminate\Database\Eloquent\Relations\HasMany\n    {\n        return \$this->hasMany(StockReceipt::class, 'batch_id');\n    }\n\n    public function ledgerEntries(): \Illuminate\Database\Eloquent\Relations\HasMany\n    {\n        return \$this->hasMany(StockLedger::class, 'batch_id');\n    }\n\n    public function adjustments(): \Illuminate\Database\Eloquent\Relations\HasMany\n    {\n        return \$this->hasMany(MedicineStockAdjustment::class, 'batch_id');\n    }\n"
];

foreach ($files as $name => $methods) {
    $path = 'app/Models/' . $name;
    $content = file_get_contents($path);
    if (strpos($content, '<<<<<<< ours') !== false) {
        // extract 'ours'
        preg_match('/<<<<<<< ours\r?\n(.*?)\r?\n=======\r?\n/s', $content, $m);
        if ($m) {
            $ours = $m[1];
            // remove last brace
            $ours = preg_replace('/}\s*$/', '', trim($ours));
            // append methods and closing brace
            $newContent = $ours . "\n\n" . $methods . "}\n";
            file_put_contents($path, $newContent);
            echo "Fixed $name\n";
        }
    }
}
