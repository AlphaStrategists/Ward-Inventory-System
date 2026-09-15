<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GeneralStockAdjustment extends Model {
    protected $table = 'general_stock_adjustments';
    public $timestamps = false;
    protected $fillable = ['item_id', 'ward_id', 'adjustment_type', 'quantity', 'reason', 'adjusted_by'];

    public function item() {
        return $this->belongsTo(GeneralItem::class, 'item_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function adjustedBy() {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}