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
        'first_name',
        'last_name',
        'is_admin',
        'email',
        'password',
        'role_id',
        'ward_id',
        'role',
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
            'is_admin' => 'boolean',
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

    public const NURSE_ROLES = ['Nurse', 'Staff Nurse', 'Ward Sister'];

    /**
     * True if the logged-in user's role name matches exactly or an equivalent nursing role.
     * Can also accept an array of role names.
     */
    public function hasRole(string|array $roleName): bool
    {
        if (is_array($roleName)) {
            return $this->hasAnyRole($roleName);
        }

        if (!$this->role) {
            return false;
        }

        if ($this->role->role === $roleName) {
            return true;
        }

        // If checking for any nursing role, allow any of the equivalent nursing roles
        if (in_array($roleName, self::NURSE_ROLES, true) && in_array($this->role->role, self::NURSE_ROLES, true)) {
            return true;
        }

        return false;
    }

    /**
     * True if the logged-in user's role name matches any in the list.
     * If the list contains any nursing role, users with any nursing role match.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        if (!$this->role) {
            return false;
        }

        if (in_array($this->role->role, $roleNames, true)) {
            return true;
        }

        if (array_intersect($roleNames, self::NURSE_ROLES) && in_array($this->role->role, self::NURSE_ROLES, true)) {
            return true;
        }

        return false;
    }

    /**
     * True if the user has any nursing role ('Nurse', 'Staff Nurse', 'Ward Sister').
     */
    public function isNurse(): bool
    {
        return $this->role && in_array($this->role->role_name, self::NURSE_ROLES, true);
    }
}
