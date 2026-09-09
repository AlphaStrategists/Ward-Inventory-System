<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surgical_consumable_1 extends Model
{
    protected $fillable = [
        'date',
        'name_of_first_aid_supply',
        'balance',
        'requested_quantity',
        'confirmation_signature_requested',
        'received_quantity',
        'confirmation_signature_received',
    ];
}
