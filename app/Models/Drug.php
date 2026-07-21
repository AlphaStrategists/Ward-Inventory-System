<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $fillable = ['name', 'unit', 'current_stock'];

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }
}
