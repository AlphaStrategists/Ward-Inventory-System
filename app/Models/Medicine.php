<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model {
    protected $table = 'medicines';
    public $timestamps = false;
    protected $fillable = ['item_code', 'name', 'category_id', 'unit_id', 'form_id', 'strength', 'is_controlled', 'min_level', 'warning_limit'];

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function unit() {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function form() {
        return $this->belongsTo(MedicineForm::class, 'form_id');
    }
    public function batches() {
        return $this->hasMany(MedicineBatch::class, 'medicine_id');
    }

    public function getStockAttribute() {
        return \App\Models\StockLedger::whereIn('batch_id', $this->batches()->pluck('id'))->sum('quantity');
    }
}