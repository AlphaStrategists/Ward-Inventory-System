<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model {
    protected $table = 'roles';
    public $timestamps = false;
    protected $fillable = ['role_name'];

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }
    public function users() {
        return $this->hasMany(User::class, 'role_id');
    }
}