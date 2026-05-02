<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'code',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'max_students',
        'max_staff',
        'max_storage_mb',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
        ];
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'subscription_plan_id');
    }

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'subscription_plan_id');
    }

    public function tenantUsageLimits(): HasMany
    {
        return $this->hasMany(TenantUsageLimit::class, 'subscription_plan_id');
    }
}
