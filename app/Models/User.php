<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;
    protected $table = 'users';
    public $timestamps = false;
    protected $fillable = ['name', 'email', 'password', 'role_id', 'ward_id'];
    protected $hidden = ['password'];

    public function role() {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function ward() {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}