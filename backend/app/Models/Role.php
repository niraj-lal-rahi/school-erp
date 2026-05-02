<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'uuid',
        'name',
        'code',
        'slug',
        'scope',
        'description',
        'role_type',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->using(RolePermission::class)
            ->withPivot(['id', 'school_id'])
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->using(UserRole::class)
            ->withPivot(['id', 'school_id', 'assigned_by'])
            ->withTimestamps();
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(RoleAuditLog::class);
    }

    public function scopeVisibleInTenant(Builder $query, ?int $schoolId): Builder
    {
        return $query->where(function (Builder $builder) use ($schoolId): void {
            $builder->whereNull('roles.school_id');

            if ($schoolId !== null) {
                $builder->orWhere('roles.school_id', $schoolId);
            }
        });
    }
}
