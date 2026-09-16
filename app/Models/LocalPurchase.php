<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalPurchase extends Model
{
    use HasFactory;

    protected $table = 'local_purchases';

    protected $fillable = [
        'name',
        'balance',
        'unit',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];
}
