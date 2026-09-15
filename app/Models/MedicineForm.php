<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicineForm extends Model {
    protected $table = 'medicine_forms';
    public $timestamps = false;
    protected $fillable = ['form_name'];

    public function medicines() {
        return $this->hasMany(Medicine::class, 'form_id');
    }
}