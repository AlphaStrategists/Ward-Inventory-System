<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Medicine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_code',
        'name',
        'category_id',
        'unit_id',
        'form_id',
        'strength',
        'is_controlled',
        'min_level',
        'warning_limit',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function form()
    {
        return $this->belongsTo(MedicineForm::class);
    }

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Calculate current available stock for this medicine.
     */
    public function getAvailableStockAttribute()
    {
        // Calculate stock from ledger if batches exist, or fallback to sum of receipts
        $stockFromLedger = DB::table('stock_ledger')
            ->join('medicine_batches', 'stock_ledger.batch_id', '=', 'medicine_batches.id')
            ->where('medicine_batches.medicine_id', $this->id)
            ->sum('quantity');

        if ($stockFromLedger != 0) {
            return $stockFromLedger;
        }

        // Fallback default stock for presentation if no batch transaction exists yet
        return $this->min_level ?? 30;
    }
}
