<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    protected $table = 'suppliers';
    public $timestamps = false;
    protected $fillable = ['supplier_name', 'contact_info'];

    public function medicineBatches() {
        return $this->hasMany(MedicineBatch::class, 'supplier_id');
    }
}