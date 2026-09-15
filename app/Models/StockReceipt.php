<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockReceipt extends Model {
    protected $table = 'stock_receipts';
    public $timestamps = false;
    protected $fillable = ['batch_id', 'ward_id', 'quantity_received', 'received_by'];

    public function batch() {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function receiver() {
        return $this->belongsTo(User::class, 'received_by');
    }
}