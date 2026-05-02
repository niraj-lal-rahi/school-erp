<?php

namespace App\Models;

use App\Models\Saas\TenantAuditLog;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantDomain;
use App\Models\Saas\TenantFeatureAccess;
use App\Models\Saas\TenantSubscription;
use App\Models\Saas\TenantUsageLimit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'email',
        'phone',
        'slug',
        'domain',
        'subdomain',
        'logo_path',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'timezone',
        'currency',
        'locale',
        'status',
        'trial_ends_at',
        'activated_at',
        'suspended_at',
        'settings',
        'storage_disk',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'trial_ends_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class, 'school_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'school_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'school_id');
    }

    public function activeTenantSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class, 'school_id')
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->latest('start_date');
    }

    public function tenantUsageLimit(): HasOne
    {
        return $this->hasOne(TenantUsageLimit::class, 'school_id');
    }

    public function tenantFeatureAccesses(): HasMany
    {
        return $this->hasMany(TenantFeatureAccess::class, 'school_id');
    }

    public function tenantBillingRecords(): HasMany
    {
        return $this->hasMany(TenantBillingRecord::class, 'school_id');
    }

    public function tenantDomains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'school_id');
    }

    public function tenantAuditLogs(): HasMany
    {
        return $this->hasMany(TenantAuditLog::class, 'school_id');
    }
}
