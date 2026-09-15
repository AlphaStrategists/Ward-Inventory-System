<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model {
    protected $table = 'medicine_batches';
    public $timestamps = false;
    protected $fillable = ['medicine_id', 'supplier_id', 'batch_no', 'expiry_date'];

    public function medicine() {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function receipts() {
        return $this->hasMany(StockReceipt::class, 'batch_id');
    }
    public function ledgerEntries() {
        return $this->hasMany(StockLedger::class, 'batch_id');
    }
    public function dispensations() {
        return $this->hasMany(Dispensation::class, 'batch_id');
    }
    public function adjustments() {
        return $this->hasMany(MedicineStockAdjustment::class, 'batch_id');
    }
}