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

    protected $casts = [
        'is_controlled' => 'boolean',
    ];

    /**
     * Current available stock: the signed sum of all ledger movements
     * (receipts positive, issues negative) across this medicine's batches.
     * A medicine with no movements has 0 stock.
     */
    public function getAvailableStockAttribute(): int
    {
        return (int) DB::table('stock_ledger')
            ->join('medicine_batches', 'stock_ledger.batch_id', '=', 'medicine_batches.id')
            ->where('medicine_batches.medicine_id', $this->id)
            ->sum('stock_ledger.quantity');
    }
}
