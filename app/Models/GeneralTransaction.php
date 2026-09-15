<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GeneralTransaction extends Model {
    protected $table = 'general_transactions';
    public $timestamps = false;
    protected $fillable = ['item_id', 'ward_id', 'quantity_received', 'quantity_issued', 'recorded_by'];

    public function item() {
        return $this->belongsTo(GeneralItem::class, 'item_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function recordedBy() {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}