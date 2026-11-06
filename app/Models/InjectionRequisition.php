<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InjectionRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'injection_requisitions';

    protected $fillable = [
        'medicine_id',
        'date',
        'requested_quantity',
        'request_signature',
        'ms_approval_status',
        'ms_approved_by',
        'ms_approved_at',
        'received_quantity',
        'issued_officer_signature',
        'issued_officer_date',
        'received_signature',
        'received_date',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'ms_approved_at' => 'datetime',
            'issued_officer_date' => 'date',
            'received_date' => 'date',
            'requested_quantity' => 'integer',
            'received_quantity' => 'integer',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(InjectionMedicine::class, 'medicine_id');
    }

    public function msApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ms_approved_by');
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
