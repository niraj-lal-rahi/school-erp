<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToSchool;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'school_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'password',
        'status',
        'last_login_at',
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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): string => $value ?: trim(($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? '')),
        );
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->using(UserRole::class)
            ->withPivot(['id', 'school_id', 'assigned_by'])
            ->withTimestamps();
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->visibleInTenant($this->school_id)
            ->whereHas('permissions', fn ($query) => $query->where('permissions.code', $permission))
            ->exists();
    }
}
