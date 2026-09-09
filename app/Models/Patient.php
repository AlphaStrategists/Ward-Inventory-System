<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    public $timestamps = false;

    protected $fillable = [
        'nic',
        'name',
        'bht',
    ];

    public function narcoticUsages()
    {
        return $this->hasMany(NarcoticUsage::class, 'patient_id', 'patient_id');
    }
    protected $fillable = [
        'name',
        'admit_date',
        'nic',
        'bedhead_number',
    ];
}
