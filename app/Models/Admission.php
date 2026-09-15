<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model {
    protected $table = 'admissions';
    public $timestamps = false;
    protected $fillable = ['patient_id', 'bht_no', 'ward_id', 'status'];

    public function patient() {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
    public function dispensations() {
        return $this->hasMany(Dispensation::class, 'admission_id');
    }
}