<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineForm extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'form_name',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }
}
