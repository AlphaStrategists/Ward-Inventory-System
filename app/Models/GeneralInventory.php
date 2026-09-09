<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralInventory extends Model
{
    use HasFactory;

    protected $table = 'general_inventory';
    protected $primaryKey = 'inventory_id';
    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'item_code',
        'entry_date',
        'received',
        'issued',
        'balance',
    ];
}
