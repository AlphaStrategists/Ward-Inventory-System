<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    protected $table = 'patients';
    const UPDATED_AT = null;

    protected $fillable = [
        'patient_name',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class, 'patient_id')->orderBy('admit_date', 'desc');
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(Admission::class, 'patient_id')
            ->where('status', 'Admitted')
            ->latestOfMany('admit_date');
    }

    public function dispensations(): HasManyThrough
    {
        return $this->hasManyThrough(Dispensation::class, Admission::class, 'patient_id', 'admission_id');
    }
}
