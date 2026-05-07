<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformTenant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

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

    public function databaseConnections(): HasMany
    {
        return $this->hasMany(TenantDatabaseConnection::class, 'tenant_id');
    }

    public function activeDatabaseConnection(): HasOne
    {
        return $this->hasOne(TenantDatabaseConnection::class, 'tenant_id')
            ->where('is_active', true)
            ->latest('id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'tenant_id');
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(TenantBillingRecord::class, 'tenant_id');
    }

    public function featureAccesses(): HasMany
    {
        return $this->hasMany(TenantFeatureAccess::class, 'tenant_id');
    }

    public function securitySetting(): HasOne
    {
        return $this->hasOne(TenantSecuritySetting::class, 'tenant_id');
    }

    public function encryptionKeys(): HasMany
    {
        return $this->hasMany(TenantEncryptionKey::class, 'tenant_id');
    }

    public function activeEncryptionKey(): HasOne
    {
        return $this->hasOne(TenantEncryptionKey::class, 'tenant_id')
            ->where('status', 'active')
            ->latest('key_version');
    }

    public function keyRotationLogs(): HasMany
    {
        return $this->hasMany(TenantKeyRotationLog::class, 'tenant_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(PlatformAuditLog::class, 'tenant_id');
    }

    public function systemHealthLogs(): HasMany
    {
        return $this->hasMany(SystemHealthLog::class, 'tenant_id');
    }

    public function emergencyAccessLogs(): HasMany
    {
        return $this->hasMany(EmergencyAccessLog::class, 'tenant_id');
    }

    public function impersonationLogs(): HasMany
    {
        return $this->hasMany(TenantImpersonationLog::class, 'tenant_id');
    }

    public function backupLogs(): HasMany
    {
        return $this->hasMany(TenantBackupLog::class, 'tenant_id');
    }
}
