<?php

namespace App\Services\Saas;

use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Models\Saas\TenantSubscription;
use App\Repositories\Contracts\Saas\TenantSubscriptionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantSubscriptionService
{
    public function __construct(
        protected TenantSubscriptionRepositoryInterface $subscriptions,
        protected TenantUsageService $usage,
        protected TenantFeatureService $features,
        protected TenantBillingService $billing,
        protected TenantAuditService $audit,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->subscriptions->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): TenantSubscription
    {
        return $this->subscriptions->findOrFail($id);
    }

    public function listByTenant(Tenant $tenant): Collection
    {
        return $this->subscriptions->listByTenant($tenant->id);
    }

    public function findActiveForTenant(Tenant $tenant): ?TenantSubscription
    {
        return $this->subscriptions->findActiveForTenant($tenant->id);
    }

    public function startTrial(Tenant $tenant, SubscriptionPlan $plan, int $trialDays = 14, $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $plan, $trialDays, $performedBy, $ipAddress): TenantSubscription {
            $subscription = $this->subscriptions->create([
                'school_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($trialDays)->toDateString(),
                'trial_ends_at' => now()->addDays($trialDays),
                'status' => 'trial',
                'auto_renew' => true,
            ]);

            $this->features->applyPlanFeatureLimits($tenant, $plan);
            $this->usage->syncUsageCounters($tenant, $plan);

            $this->audit->log('subscription.trial_started', $tenant->id, 'Trial subscription started.', [], $subscription->toArray(), $performedBy, $ipAddress);

            return $subscription;
        });
    }

    public function subscribeTenant(Tenant $tenant, SubscriptionPlan $plan, array $attributes, $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $plan, $attributes, $performedBy, $ipAddress): TenantSubscription {
            $subscription = $this->subscriptions->create([
                'school_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $attributes['billing_cycle'],
                'start_date' => $attributes['start_date'],
                'end_date' => $attributes['end_date'] ?? null,
                'trial_ends_at' => $attributes['trial_ends_at'] ?? null,
                'status' => $attributes['status'] ?? 'active',
                'auto_renew' => (bool) ($attributes['auto_renew'] ?? true),
            ]);

            $this->features->applyPlanFeatureLimits($tenant, $plan);
            $this->usage->syncUsageCounters($tenant, $plan);
            $this->billing->generateBillingRecord($tenant, $subscription);

            $this->audit->log('subscription.created', $tenant->id, 'Tenant subscribed to plan.', [], $subscription->toArray(), $performedBy, $ipAddress);

            return $subscription;
        });
    }

    public function changePlan(Tenant $tenant, SubscriptionPlan $plan, array $attributes = [], $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $plan, $attributes, $performedBy, $ipAddress): TenantSubscription {
            $subscription = $this->subscriptions->findActiveForTenant($tenant->id);

            if ($subscription) {
                $this->subscriptions->update($subscription, [
                    'status' => 'cancelled',
                    'end_date' => $attributes['start_date'] ?? now()->toDateString(),
                    'auto_renew' => false,
                ]);
            }

            return $this->subscribeTenant($tenant, $plan, array_merge([
                'billing_cycle' => $attributes['billing_cycle'] ?? 'monthly',
                'start_date' => $attributes['start_date'] ?? now()->toDateString(),
                'status' => $attributes['status'] ?? 'active',
                'auto_renew' => $attributes['auto_renew'] ?? true,
            ], $attributes), $performedBy, $ipAddress);
        });
    }

    public function renewSubscription(TenantSubscription $subscription, array $attributes = [], $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return DB::transaction(function () use ($subscription, $attributes, $performedBy, $ipAddress): TenantSubscription {
            $oldValues = $subscription->toArray();

            $currentEnd = $subscription->end_date ?? now()->toDateString();
            $baseDate = Carbon::parse($currentEnd);
            $renewedEnd = $subscription->billing_cycle === 'yearly'
                ? $baseDate->copy()->addYear()->toDateString()
                : $baseDate->copy()->addMonth()->toDateString();

            $subscription = $this->subscriptions->update($subscription, [
                'status' => 'active',
                'end_date' => $attributes['end_date'] ?? $renewedEnd,
                'auto_renew' => $attributes['auto_renew'] ?? $subscription->auto_renew,
            ]);

            $this->billing->generateBillingRecord($subscription->tenant, $subscription);
            $this->audit->log('subscription.renewed', $subscription->school_id, 'Subscription renewed.', $oldValues, $subscription->toArray(), $performedBy, $ipAddress);

            return $subscription;
        });
    }

    public function cancelSubscription(TenantSubscription $subscription, $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return $this->updateSubscriptionStatus($subscription, 'cancelled', $performedBy, $ipAddress);
    }

    public function expireSubscription(TenantSubscription $subscription, $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return $this->updateSubscriptionStatus($subscription, 'expired', $performedBy, $ipAddress);
    }

    protected function updateSubscriptionStatus(TenantSubscription $subscription, string $status, $performedBy = null, ?string $ipAddress = null): TenantSubscription
    {
        return DB::transaction(function () use ($subscription, $status, $performedBy, $ipAddress): TenantSubscription {
            $oldValues = $subscription->toArray();
            $subscription = $this->subscriptions->update($subscription, [
                'status' => $status,
                'auto_renew' => false,
            ]);

            $this->audit->log("subscription.{$status}", $subscription->school_id, "Subscription marked {$status}.", $oldValues, $subscription->toArray(), $performedBy, $ipAddress);

            return $subscription;
        });
    }
}
