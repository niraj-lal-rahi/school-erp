<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\PlanFeature;
use App\Modules\SuperAdmin\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Models\TenantFeatureAccess;
use App\Modules\SuperAdmin\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantSubscriptionService
{
    protected string $platformConnection = 'platform';

    public function findActiveForTenant(PlatformTenant $tenant): ?TenantSubscription
    {
        return TenantSubscription::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function subscribeTenant(
        PlatformTenant $tenant,
        SubscriptionPlan $plan,
        array $attributes,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantSubscription {
        return DB::connection($this->platformConnection)->transaction(function () use ($tenant, $plan, $attributes, $performedByUserId, $ipAddress, $userAgent): TenantSubscription {
            $current = $this->findActiveForTenant($tenant);

            if ($current) {
                $current->update([
                    'status' => 'cancelled',
                    'auto_renew' => false,
                ]);
            }

            $billingCycle = $attributes['billing_cycle'] ?? 'monthly';
            $startDate = ! empty($attributes['start_date']) ? now()->parse($attributes['start_date']) : now();
            $isTrial = ! empty($attributes['trial_ends_at']);
            $endDate = ! empty($attributes['end_date'])
                ? now()->parse($attributes['end_date'])
                : ($billingCycle === 'yearly' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth());

            $subscription = TenantSubscription::query()->create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'subscription_code' => 'SUB-'.Str::upper(Str::random(10)),
                'billing_cycle' => $billingCycle,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'trial_ends_at' => $attributes['trial_ends_at'] ?? null,
                'status' => $isTrial ? 'trial' : ($attributes['status'] ?? 'active'),
                'auto_renew' => (bool) ($attributes['auto_renew'] ?? true),
                'next_billing_at' => $attributes['next_billing_at'] ?? $endDate,
            ]);

            $this->syncTenantFeatureAccess($tenant, $plan);

            $this->logAction($tenant->id, 'tenant_subscription_created', 'tenant_subscription', 'Tenant subscription created.', [
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $subscription->load(['subscriptionPlan'])->loadCount(['billingRecords']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function changePlan(
        PlatformTenant $tenant,
        SubscriptionPlan $plan,
        array $attributes,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantSubscription {
        return DB::connection($this->platformConnection)->transaction(function () use ($tenant, $plan, $attributes, $performedByUserId, $ipAddress, $userAgent): TenantSubscription {
            $subscription = $this->findActiveForTenant($tenant);

            if (! $subscription) {
                return $this->subscribeTenant($tenant, $plan, $attributes, $performedByUserId, $ipAddress, $userAgent);
            }

            $subscription->update([
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $attributes['billing_cycle'] ?? $subscription->billing_cycle,
                'status' => $attributes['status'] ?? 'active',
                'end_date' => $attributes['end_date'] ?? $subscription->end_date,
                'trial_ends_at' => $attributes['trial_ends_at'] ?? $subscription->trial_ends_at,
                'auto_renew' => (bool) ($attributes['auto_renew'] ?? $subscription->auto_renew),
                'next_billing_at' => $attributes['next_billing_at'] ?? $subscription->next_billing_at,
            ]);

            $this->syncTenantFeatureAccess($tenant, $plan);

            $this->logAction($tenant->id, 'tenant_subscription_plan_changed', 'tenant_subscription', 'Tenant plan changed.', [
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $subscription->fresh(['subscriptionPlan'])->loadCount(['billingRecords']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function renewSubscription(
        TenantSubscription $subscription,
        array $attributes,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantSubscription {
        return DB::connection($this->platformConnection)->transaction(function () use ($subscription, $attributes, $performedByUserId, $ipAddress, $userAgent): TenantSubscription {
            $subscription->update([
                'end_date' => $attributes['end_date'] ?? $subscription->end_date,
                'auto_renew' => array_key_exists('auto_renew', $attributes)
                    ? (bool) $attributes['auto_renew']
                    : $subscription->auto_renew,
                'status' => 'active',
                'next_billing_at' => $attributes['end_date'] ?? $subscription->next_billing_at,
            ]);

            $this->logAction($subscription->tenant_id, 'tenant_subscription_renewed', 'tenant_subscription', 'Tenant subscription renewed.', [
                'subscription_id' => $subscription->id,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $subscription->fresh(['subscriptionPlan'])->loadCount(['billingRecords']);
        });
    }

    public function cancelSubscription(
        TenantSubscription $subscription,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantSubscription {
        return DB::connection($this->platformConnection)->transaction(function () use ($subscription, $performedByUserId, $ipAddress, $userAgent): TenantSubscription {
            $subscription->update([
                'status' => 'cancelled',
                'auto_renew' => false,
            ]);

            $this->logAction($subscription->tenant_id, 'tenant_subscription_cancelled', 'tenant_subscription', 'Tenant subscription cancelled.', [
                'subscription_id' => $subscription->id,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $subscription->fresh(['subscriptionPlan'])->loadCount(['billingRecords']);
        });
    }

    protected function syncTenantFeatureAccess(PlatformTenant $tenant, SubscriptionPlan $plan): void
    {
        $features = PlanFeature::query()
            ->where('subscription_plan_id', $plan->id)
            ->get();

        $featureCodes = $features->pluck('feature_code')->all();

        if ($featureCodes !== []) {
            TenantFeatureAccess::query()
                ->where('tenant_id', $tenant->id)
                ->whereNotIn('feature_code', $featureCodes)
                ->where('access_source', 'plan')
                ->delete();
        }

        foreach ($features as $feature) {
            TenantFeatureAccess::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'feature_code' => $feature->feature_code,
                ],
                [
                    'subscription_plan_id' => $plan->id,
                    'module' => $feature->module,
                    'is_enabled' => (bool) $feature->is_enabled,
                    'limit_value' => $feature->limit_value,
                    'access_source' => 'plan',
                    'metadata' => $feature->config,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        int $tenantId,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
