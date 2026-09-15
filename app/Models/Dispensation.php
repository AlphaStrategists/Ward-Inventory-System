<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Dispensation extends Model {
    protected $table = 'dispensations';
    public $timestamps = false;
    protected $fillable = ['admission_id', 'batch_id', 'qty_given', 'dosage', 'usage_time', 'issued_by', 'witnessed_by'];

    public function admission() {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
    public function batch() {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
    public function issuedBy() {
        return $this->belongsTo(User::class, 'issued_by');
    }
    public function witnessedBy() {
        return $this->belongsTo(User::class, 'witnessed_by');
    }
}