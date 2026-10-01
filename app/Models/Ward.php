<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ward_number',
        'ward_name',
    ];
}
