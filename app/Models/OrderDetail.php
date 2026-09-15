<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {
    protected $table = 'order_details';
    public $timestamps = false;
    protected $fillable = ['order_id', 'medicine_id', 'qty_requested', 'qty_issued', 'remark'];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function medicine() {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}