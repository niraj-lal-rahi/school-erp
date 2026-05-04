<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

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
            'max_students' => 'integer',
            'max_staff' => 'integer',
            'max_storage_mb' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'subscription_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'subscription_plan_id');
    }

    public function tenantFeatureAccesses(): HasMany
    {
        return $this->hasMany(TenantFeatureAccess::class, 'subscription_plan_id');
    }
}
