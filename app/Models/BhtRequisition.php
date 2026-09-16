<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhtRequisition extends Model
{
    use HasFactory;

    protected $table = 'bht_requisitions';

    protected $fillable = [
        'category_type', // 'surgical_consumable_2' or 'local_purchases'
        'item_name',
        'date',
        'bht_no',
        'quantity',
        'balance',
        'signature_in_charge',
        'officer_name',
        'note',
    ];
}
