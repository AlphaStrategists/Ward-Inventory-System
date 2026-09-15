<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GeneralItem extends Model {
    protected $table = 'general_items';
    public $timestamps = false;
    protected $fillable = ['item_code', 'name', 'category_id'];

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }
}