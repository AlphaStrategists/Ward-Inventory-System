<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicineStockAdjustment extends Model {
    protected $table = 'medicine_stock_adjustments';
    public $timestamps = false;
    protected $fillable = ['batch_id', 'ward_id', 'adjustment_type', 'quantity', 'reason', 'adjusted_by'];

    public function batch() {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function adjustedBy() {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}