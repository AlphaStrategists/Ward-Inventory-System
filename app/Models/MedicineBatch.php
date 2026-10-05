<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    protected $table = 'medicine_batches';
    const UPDATED_AT = null;

    protected $fillable = [
        'medicine_id',
        'supplier_id',
        'batch_no',
        'expiry_date',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function dispensations(): HasMany
    {
        return $this->hasMany(Dispensation::class, 'batch_id');
    }

    public function stockLedger(): HasMany
    {
        return $this->hasMany(StockLedger::class, 'batch_id');
    }

    /**
     * Helper to get batch stock balance from stock_ledger
     */
    public function getCurrentBalanceAttribute(): int
    {
        return (int) $this->stockLedger()->sum('quantity');
    }
}
