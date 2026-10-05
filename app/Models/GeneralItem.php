<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralItem extends Model
{
    protected $table = 'general_items';
    public $timestamps = false;

    // TODO: Add a min_level / warning_limit column to general_items in future migration if configurable thresholds are needed
    const DEFAULT_LOW_STOCK_THRESHOLD = 10;

    protected $fillable = [
        'item_code',
        'name',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GeneralTransaction::class, 'item_id');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(GeneralStockAdjustment::class, 'item_id');
    }

    /**
     * Compute current stock balance: SUM(quantity_received) - SUM(quantity_issued)
     * Also factors in adjustments if present.
     */
    public function getCurrentBalanceAttribute(): int
    {
        if (isset($this->attributes['computed_balance'])) {
            return (int) $this->attributes['computed_balance'];
        }

        $received = (int) $this->transactions()->sum('quantity_received');
        $issued = (int) $this->transactions()->sum('quantity_issued');
        $adjustments = (int) $this->stockAdjustments()->sum('quantity');

        return ($received - $issued) + $adjustments;
    }

    /**
     * Check if item is low on stock
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_balance <= self::DEFAULT_LOW_STOCK_THRESHOLD;
    }
}
