<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {
    protected $table = 'activity_logs';
    public $timestamps = false;
    protected $fillable = ['user_id', 'action_performed', 'ip_address'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}