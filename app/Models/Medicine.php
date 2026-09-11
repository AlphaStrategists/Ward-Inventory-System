<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $guarded = [];

    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function form() { return $this->belongsTo(MedicineForm::class, 'form_id'); }
    
    public function batches() { return $this->hasMany(MedicineBatch::class); }

    public function getStockAttribute()
    {
        return $this->batches()
            ->join('stock_ledger', 'medicine_batches.id', '=', 'stock_ledger.batch_id')
            ->sum('stock_ledger.quantity');
    }

    public function getStockStatusAttribute()
    {
        $stock = $this->stock;
        if ($stock <= $this->min_level) return 'low';
        if ($stock <= $this->warning_limit) return 'warning';
        return 'sufficient';
    }
}
