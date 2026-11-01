<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStockAdjustment extends Model
{
    protected $table = 'medicine_stock_adjustments';
    const UPDATED_AT = null;

    protected $fillable = [
        'batch_id',
        'ward_id',
        'adjustment_type',
        'type',
        'quantity',
        'reason',
        'adjusted_by',
        'created_at',
    ];

    public function getTypeAttribute(): string
    {
        return in_array($this->adjustment_type, ['EXPIRY', 'DAMAGED', 'LOST']) ? 'deduction' : 'addition';
    }

    public function setTypeAttribute($value): void
    {
        if (strtolower($value) === 'deduction') {
            $this->attributes['adjustment_type'] = 'EXPIRY';
        } else {
            $this->attributes['adjustment_type'] = strtoupper($value);
        }
    }

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

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
