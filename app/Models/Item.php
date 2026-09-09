<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'item_type',
        'item_subtype',
        'workflow_pattern',
        'quantity',
    ];

    public function requestedStocks()
    {
        return $this->hasMany(RequestedStock::class, 'item_id', 'item_id');
    }

    public function narcoticUsages()
    {
        return $this->hasMany(NarcoticUsage::class, 'item_id', 'item_id');
    }

    public function consumableUsages()
    {
        return $this->hasMany(ConsumableUsage::class, 'item_id', 'item_id');
    }

    /**
     * Get active transactions dynamic relationship/collection based on workflow_pattern.
     */
    public function getActiveTransactionsAttribute()
    {
        return match ($this->workflow_pattern) {
            'request_simple', 'request_approved' => $this->requestedStocks,
            'narcotic_direct' => $this->narcoticUsages,
            'consumable_direct' => $this->consumableUsages,
            default => collect(),
        };
    }
}
