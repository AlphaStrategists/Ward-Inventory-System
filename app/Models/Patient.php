<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'admit_date',
        'nic',
        'bedhead_number',
    ];
}
