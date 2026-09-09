<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'role',
    ];

    public function requestedStocks()
    {
        return $this->hasMany(RequestedStock::class, 'requested_by_staff_id', 'staff_id');
    }

    public function confirmRequestedStocks()
    {
        return $this->hasMany(RequestedStock::class, 'confirm_request_staff_id', 'staff_id');
    }

    public function approvedMsStocks()
    {
        return $this->hasMany(RequestedStock::class, 'approved_by_ms_staff_id', 'staff_id');
    }

    public function issuedStocks()
    {
        return $this->hasMany(RequestedStock::class, 'issued_officer_staff_id', 'staff_id');
    }

    public function confirmReceivedStocks()
    {
        return $this->hasMany(RequestedStock::class, 'confirm_received_staff_id', 'staff_id');
    }

    public function narcoticUsages()
    {
        return $this->hasMany(NarcoticUsage::class, 'recorded_by_staff_id', 'staff_id');
    }

    public function consumableUsages()
    {
        return $this->hasMany(ConsumableUsage::class, 'incharge_staff_id', 'staff_id');
    }
}
