<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model {
    protected $table = 'units';
    public $timestamps = false;
    protected $fillable = ['unit_name'];

    public function medicines() {
        return $this->hasMany(Medicine::class, 'unit_id');
    }
}