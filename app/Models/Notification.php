<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    protected $table = 'notifications';
    public $timestamps = false;
    protected $fillable = ['user_id', 'message', 'is_read'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}