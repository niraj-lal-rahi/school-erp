<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantSubscription extends Model
{
    use SoftDeletes;

    protected $table = 'tenant_subscriptions';

    protected $fillable = [
        'school_id',
        'subscription_plan_id',
        'billing_cycle',
        'start_date',
        'end_date',
        'trial_ends_at',
        'status',
        'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'trial_ends_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(TenantBillingRecord::class, 'subscription_id');
    }
}
