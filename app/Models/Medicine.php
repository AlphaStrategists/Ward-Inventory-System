<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $table = 'medicines';
    const UPDATED_AT = null;

    protected $fillable = [
        'item_code',
        'name',
        'category_id',
        'unit_id',
        'form_id',
        'strength_value',
        'strength_unit_id',
        'is_controlled',
        'min_level',
        'warning_limit',
        'units_per_pack',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'strength_value' => 'float',
            'is_controlled' => 'boolean',
            'min_level' => 'integer',
            'warning_limit' => 'integer',
            'units_per_pack' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(MedicineForm::class, 'form_id');
    }

    public function strengthUnit(): BelongsTo
    {
        return $this->belongsTo(StrengthUnit::class, 'strength_unit_id');
    }

    public function getStrengthAttribute(): ?string
    {
        if ($this->strength_value === null) {
            return $this->attributes['strength'] ?? null;
        }

        $formattedVal = rtrim(rtrim(number_format((float) $this->strength_value, 2, '.', ''), '0'), '.');
        $unitName = $this->strengthUnit?->name ?? '';

        return $formattedVal . $unitName;
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class, 'medicine_id');
    }

    public function scopeControlled(Builder $query): Builder
    {
        return $query->where('is_controlled', 1);
    }

    public function getStockAttribute()
    {
        return \App\Models\StockLedger::whereIn('batch_id', $this->batches()->pluck('id'))->sum('quantity');
    }
}
