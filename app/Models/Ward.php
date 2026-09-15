<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model {
    protected $table = 'wards';
    public $timestamps = false;
    protected $fillable = ['ward_number', 'ward_name'];

    public function users() {
        return $this->hasMany(User::class, 'ward_id');
    }
    public function admissions() {
        return $this->hasMany(Admission::class, 'ward_id');
    }
    public function stockReceipts() {
        return $this->hasMany(StockReceipt::class, 'ward_id');
    }
}