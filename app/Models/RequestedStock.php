<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedStock extends Model
{
    use HasFactory;

    protected $table = 'requested_stock';
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'requested_by_staff_id',
        'request_date',
        'required_quantity',
        'balance_before',
        'status',
        'confirm_request_staff_id',
        'approved_by_ms_staff_id',
        'received_quantity',
        'received_date',
        'confirm_received_staff_id',
        'issued_officer_staff_id',
        'issued_date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(Staff::class, 'requested_by_staff_id', 'staff_id');
    }

    public function confirmRequestBy()
    {
        return $this->belongsTo(Staff::class, 'confirm_request_staff_id', 'staff_id');
    }

    public function approvedByMs()
    {
        return $this->belongsTo(Staff::class, 'approved_by_ms_staff_id', 'staff_id');
    }

    public function confirmReceivedBy()
    {
        return $this->belongsTo(Staff::class, 'confirm_received_staff_id', 'staff_id');
    }

    public function issuedOfficer()
    {
        return $this->belongsTo(Staff::class, 'issued_officer_staff_id', 'staff_id');
    }
}
