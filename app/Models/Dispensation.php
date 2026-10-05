<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensation extends Model
{
    protected $table = 'dispensations';
    public $timestamps = false;

    protected $fillable = [
        'admission_id',
        'batch_id',
        'date',
        'qty_given',
        'dosage',
        'usage_time',
        'issued_by',
        'witnessed_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'qty_given' => 'integer',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function witnessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }
}
