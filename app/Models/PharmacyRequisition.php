<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyRequisition extends Model
{
    use HasFactory;

    protected $table = 'pharmacy_requisitions';

    protected $fillable = [
        'date',
        'supply_name',
        'balance',
        'requested_qty',
        'requested_by',
        'request_signature',
        'received_qty',
        'received_by',
        'receive_signature',
    ];
}
