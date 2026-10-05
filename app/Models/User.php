<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'ward_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function generalTransactions(): HasMany
    {
        return $this->hasMany(GeneralTransaction::class, 'recorded_by');
    }

    public function generalStockAdjustments(): HasMany
    {
        return $this->hasMany(GeneralStockAdjustment::class, 'adjusted_by');
    }

    public function dispensationsIssued(): HasMany
    {
        return $this->hasMany(Dispensation::class, 'issued_by');
    }

        public function dispensationsWitnessed(): HasMany
    {
        return $this->hasMany(Dispensation::class, 'witnessed_by');
    }

    /**
     * True if the logged-in user's role name matches exactly.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->role_name === $roleName;
    }

    /**
     * True if the logged-in user's role name matches any in the list.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->role_name, $roleNames, true);
    }
}
