<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformTenant extends Model
{
    use SoftDeletes;

    protected $connection = 'platform';

    protected $table = 'platform_tenants';

    protected $fillable = [
        'name',
        'code',
        'slug',
        'email',
        'phone',
        'status',
        'trial_ends_at',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function databaseConnection(): HasOne
    {
        return $this->hasOne(TenantDatabaseConnection::class, 'tenant_id')
            ->where('is_active', true)
            ->latest('id');
    }

    public function securitySetting(): HasOne
    {
        return $this->hasOne(TenantSecuritySetting::class, 'tenant_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(PlatformAuditLog::class, 'tenant_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasDatabaseConnection(): bool
    {
        if ($this->relationLoaded('databaseConnection')) {
            return $this->databaseConnection !== null;
        }

        return $this->databaseConnection()->exists();
    }
}
