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
        'balance',
        'unit',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];
}
