<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsageLimit extends Model
{
    protected $table = 'tenant_usage_limits';

    protected $fillable = [
        'school_id',
        'subscription_plan_id',
        'max_students',
        'max_staff',
        'max_storage_mb',
        'current_students',
        'current_staff',
        'current_storage_mb',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
