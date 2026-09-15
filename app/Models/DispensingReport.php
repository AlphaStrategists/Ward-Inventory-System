<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DispensingReport extends Model {
    protected $table = 'dispensing_reports';
    public $timestamps = false;
    protected $fillable = ['report_title', 'start_date', 'end_date', 'generated_by', 'file_path'];

    public function generatedBy() {
        return $this->belongsTo(User::class, 'generated_by');
    }
}