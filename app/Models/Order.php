<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $table = 'orders';
    public $timestamps = false;
    protected $fillable = ['ward_id', 'req_no', 'requested_by', 'ms_approval_status', 'approved_by', 'remark'];

    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function requester() {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function details() {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}