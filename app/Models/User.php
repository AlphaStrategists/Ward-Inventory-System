<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    const UPDATED_AT = null;

    public const ROLES = [
        'Doctor',
        'MS',
        'Nurse',
        'Head Nurse',
        'Pharmacist',
    ];

    public const NURSE_ROLES = [
        'Nurse',
        'Head Nurse',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'is_admin',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Allow views to use $user->name.
     */
    protected function name(): Attribute
    {
        return Attribute::get(
            fn () => trim(
                ($this->first_name ?? '') . ' ' .
                ($this->last_name ?? '')
            )
        );
    }

    /**
     * Role is stored directly in users.role.
     */
    public function getRoleAttribute(): ?string
    {
        if (isset($this->attributes['role']) && trim((string) $this->attributes['role']) !== '') {
            return trim((string) $this->attributes['role']);
        }

        if ($this->relationLoaded('roleRelation') || !empty($this->attributes['role_id'])) {
            $relatedRole = $this->roleRelation?->role_name;
            if ($relatedRole !== null && trim((string) $relatedRole) !== '') {
                return trim((string) $relatedRole);
            }
        }

        if (!empty($this->attributes['is_admin'])) {
            return 'Admin';
        }

        return null;
    }

    /**
     * Find the matching role record using role_name.
     * Does not require users.role_id.
     */
    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'role',
            'role_name'
        );
    }

    public function generalTransactions(): HasMany
    {
        return $this->hasMany(
            GeneralTransaction::class,
            'recorded_by'
        );
    }

    public function generalStockAdjustments(): HasMany
    {
        return $this->hasMany(
            GeneralStockAdjustment::class,
            'adjusted_by'
        );
    }

    public function dispensationsIssued(): HasMany
    {
        return $this->hasMany(
            Dispensation::class,
            'issued_by'
        );
    }

    public function dispensationsWitnessed(): HasMany
    {
        return $this->hasMany(
            Dispensation::class,
            'witnessed_by'
        );
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    private static function normalize(string $value): string
    {
        return strtolower(
            preg_replace('/[\s_-]+/', ' ', trim($value))
        );
    }

    /**
     * Check whether the user has one of the requested roles.
     */
    public function hasRole(string|array $roleName): bool
    {
        return $this->hasAnyRole((array) $roleName);
    }

    public function hasAnyRole(array $roleNames): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $current = self::normalize($this->role ?? '');

        if ($current === '') {
            return false;
        }

        // Doctor and MS represent the same application role.
        if ($current === 'doctor') {
            $current = 'ms';
        }

        $wanted = array_map(
            function ($role) {
                $role = self::normalize((string) $role);

                return $role === 'doctor' ? 'ms' : $role;
            },
            $roleNames
        );

        // Head Nurse can perform Nurse-level actions.
        if (
            $current === 'head nurse' &&
            in_array('nurse', $wanted, true)
        ) {
            return true;
        }

        return in_array($current, $wanted, true);
    }

    public function isNurse(): bool
    {
        return $this->hasRole(self::NURSE_ROLES);
    }
}
