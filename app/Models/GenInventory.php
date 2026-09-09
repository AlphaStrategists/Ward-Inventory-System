<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenInventory extends Model
{
    protected $table = 'gen_inventory';

    protected $fillable = [
        'item',
        'received date',
        'code',
        'received',
        'issued',
        'issued date',
    ];

    protected $appends = ['balance'];

    public function getBalanceAttribute()
    {
        return $this->received - $this->issued;
    }
}