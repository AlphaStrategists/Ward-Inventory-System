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

    public const ROLES = ['Nurse', 'Staff Nurse', 'Ward Sister'];   // add your other roles here
    public const NURSE_ROLES = ['Nurse', 'Staff Nurse', 'Ward Sister'];

    protected $fillable = [
        'first_name',
        'last_name',
        'is_admin',
        'email',
        'password',
        'role',
        'role_id',
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

    /** Lets views use $user->name even though the table has first_name and last_name. */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name . ' ' . $this->last_name) ?: ($this->attributes['name'] ?? ''));
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleAttribute(): ?string
    {
        if (isset($this->attributes['role']) && !empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        return $this->roleRelation?->role_name;
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

    /** is_admin only controls user management. */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    private static function normalize(string $value): string
    {
        return strtolower(str_replace(['_', '-'], ' ', trim($value)));
    }

    public function hasRole(string|array $roleName): bool
    {
        return $this->hasAnyRole((array) $roleName);
    }

    /**
     * True if the user's role matches any name in the list.
     * If the list contains a nursing role, any nursing role matches.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        $roleName = $this->role ?? $this->roleRelation?->role_name;
        if (! $roleName) {
            return false;
        }

        $current = self::normalize($roleName);
        $wanted  = array_map([self::class, 'normalize'], $roleNames);
        $nurse   = array_map([self::class, 'normalize'], self::NURSE_ROLES);

        if (in_array($current, $wanted, true)) {
            return true;
        }

        return ! empty(array_intersect($wanted, $nurse)) && in_array($current, $nurse, true);
    }

    public function isNurse(): bool
    {
        return $this->hasAnyRole(self::NURSE_ROLES);
    }
}