<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InjectionAntibioticRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'injection_antibiotic_requisitions';

    protected $fillable = [
        'medicine_id',
        'date',
        'balance',
        'required_quantity',
        'request_signature',
        'received_quantity',
        'received_signature',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'balance' => 'integer',
            'required_quantity' => 'integer',
            'received_quantity' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(InjectionMedicine::class, 'medicine_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
