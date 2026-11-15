<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrengthUnit extends Model
{
    protected $table = 'strength_units';
    public $timestamps = false;
    protected $fillable = ['name'];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'strength_unit_id');
    }
}
