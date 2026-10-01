<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'unit_name',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }
}
