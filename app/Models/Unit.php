<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $table = 'units';
    public $timestamps = false;

    protected $fillable = [
        'unit_name',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'unit_id');
    }
}
