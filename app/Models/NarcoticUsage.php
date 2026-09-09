<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NarcoticUsage extends Model
{
    use HasFactory;

    protected $table = 'narcotic_usage';
    protected $primaryKey = 'usage_id';
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'patient_id',
        'bed_no',
        'usage_date',
        'usage_time',
        'dosage',
        'recorded_by_staff_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(Staff::class, 'recorded_by_staff_id', 'staff_id');
    }
}
