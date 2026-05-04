<?php

namespace App\Modules\SuperAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantSubscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'platform';

    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'subscription_code',
        'billing_cycle',
        'start_date',
        'end_date',
        'trial_ends_at',
        'status',
        'auto_renew',
        'next_billing_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'trial_ends_at' => 'datetime',
            'auto_renew' => 'boolean',
            'next_billing_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PlatformTenant::class, 'tenant_id');
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
