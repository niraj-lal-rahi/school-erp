<?php

namespace App\Models\Saas;

use App\Models\School;

class Tenant extends School
{
    protected $table = 'schools';

    public function subscriptions()
    {
        return $this->tenantSubscriptions();
    }

    public function activeSubscription()
    {
        return $this->activeTenantSubscription();
    }

    public function usageLimit()
    {
        return $this->tenantUsageLimit();
    }

    public function featureAccesses()
    {
        return $this->tenantFeatureAccesses();
    }

    public function billingRecords()
    {
        return $this->tenantBillingRecords();
    }

    public function domains()
    {
        return $this->tenantDomains();
    }

    public function auditLogs()
    {
        return $this->tenantAuditLogs();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function hasFeature(string $featureCode): bool
    {
        $explicitAccess = $this->relationLoaded('featureAccesses')
            ? $this->featureAccesses->firstWhere('feature_code', $featureCode)
            : $this->featureAccesses()->where('feature_code', $featureCode)->first();

        if ($explicitAccess !== null) {
            return (bool) $explicitAccess->is_enabled;
        }

        $subscription = $this->relationLoaded('activeSubscription')
            ? $this->activeSubscription
            : $this->activeSubscription()->with('subscriptionPlan.planFeatures')->first();

        if (! $subscription || ! $subscription->subscriptionPlan) {
            return false;
        }

        $feature = $subscription->subscriptionPlan->relationLoaded('planFeatures')
            ? $subscription->subscriptionPlan->planFeatures->firstWhere('feature_code', $featureCode)
            : $subscription->subscriptionPlan->planFeatures()->where('feature_code', $featureCode)->first();

        return (bool) ($feature?->is_enabled);
    }
}
