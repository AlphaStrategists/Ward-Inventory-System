<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $table = 'categories';
    public $timestamps = false;
    protected $fillable = ['name', 'description'];

    public function medicines() {
        return $this->hasMany(Medicine::class, 'category_id');
    }
    public function generalItems() {
        return $this->hasMany(GeneralItem::class, 'category_id');
    }
}