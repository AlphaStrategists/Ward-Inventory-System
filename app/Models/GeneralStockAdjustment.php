<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralStockAdjustment extends Model
{
    protected $table = 'general_stock_adjustments';
    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'ward_id',
        'adjustment_type',
        'quantity',
        'reason',
        'adjusted_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GeneralItem::class, 'item_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
