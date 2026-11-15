<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    protected $table = 'admissions';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'bht_no',
        'ward_id',
        'admit_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'admit_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function dispensations(): HasMany
    {
        return $this->hasMany(Dispensation::class, 'admission_id')->orderBy('date', 'desc');
    }
}
