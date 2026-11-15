<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineForm extends Model
{
    protected $table = 'medicine_forms';
    public $timestamps = false;

    protected $fillable = [
        'form_name',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'form_id');
    }
}
