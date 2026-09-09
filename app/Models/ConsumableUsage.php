<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableUsage extends Model
{
    use HasFactory;

    protected $table = 'consumable_usage';
    protected $primaryKey = 'usage_id';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'bed_head_no',
        'usage_date',
        'quantity',
        'balance',
        'incharge_staff_id',
        'notes',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function inchargeStaff()
    {
        return $this->belongsTo(Staff::class, 'incharge_staff_id', 'staff_id');
    }
}
