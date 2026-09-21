<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgicalConsumable extends Model
{
    use HasFactory;

    protected $table = 'surgical_consumables';

    protected $fillable = [
        'name',
        'type', // 'consumable_1' or 'consumable_2'
        'balance',
        'unit',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];
}
