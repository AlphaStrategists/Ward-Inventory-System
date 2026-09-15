<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model {
    protected $table = 'permissions';
    public $timestamps = false;
    protected $fillable = ['permission_name'];

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}