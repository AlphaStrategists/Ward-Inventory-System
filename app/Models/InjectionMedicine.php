<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InjectionMedicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'injection_medicines';

    protected $fillable = [
        'type',
        'name',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function injectionRequisitions(): HasMany
    {
        return $this->hasMany(InjectionRequisition::class, 'medicine_id');
    }

    public function antibioticRequisitions(): HasMany
    {
        return $this->hasMany(InjectionAntibioticRequisition::class, 'medicine_id');
    }
}
