<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    protected $table = 'stock_ledger';
    const UPDATED_AT = null;

    /**
     * Note: This table is auto-populated by DB triggers (trg_receipt_ledger, trg_dispensation_ledger, trg_adjustment_ledger)
     * and is primarily read-only from the application side.
     */
    protected $fillable = [
        'batch_id',
        'ward_id',
        'transaction_type',
        'quantity',
        'source_table',
        'source_id',
        'recorded_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
