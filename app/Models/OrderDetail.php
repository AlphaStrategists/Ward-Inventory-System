<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'medicine_id',
        'qty_requested',
        'qty_issued',
        'qty_received',
        'balance_before_order',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'qty_requested' => 'integer',
            'qty_issued' => 'integer',
            'qty_received' => 'integer',
            'balance_before_order' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
