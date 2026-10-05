<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    protected $table = 'wards';
    public $timestamps = false;

    protected $fillable = [
        'ward_number',
        'ward_name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'ward_id');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class, 'ward_id');
    }

    public function generalTransactions(): HasMany
    {
        return $this->hasMany(GeneralTransaction::class, 'ward_id');
    }

    public function generalStockAdjustments(): HasMany
    {
        return $this->hasMany(GeneralStockAdjustment::class, 'ward_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'ward_id');
    }
}
