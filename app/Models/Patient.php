<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model {
    protected $table = 'patients';
    public $timestamps = false;
    protected $fillable = ['patient_name'];

    public function admissions() {
        return $this->hasMany(Admission::class, 'patient_id');
    }
}