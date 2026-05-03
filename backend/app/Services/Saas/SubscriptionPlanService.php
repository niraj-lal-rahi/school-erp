<?php

namespace App\Services\Saas;

use App\Models\Saas\SubscriptionPlan;
use App\Repositories\Contracts\Saas\SubscriptionPlanRepositoryInterface;
use App\Services\Cache\CacheInvalidationService;
use App\Services\Cache\TenantCacheService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanService
{
    public function __construct(
        protected SubscriptionPlanRepositoryInterface $plans,
        protected TenantAuditService $audit,
        protected TenantCacheService $cache,
        protected CacheInvalidationService $invalidator,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->plans->paginate($filters, $perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->plans->all($filters);
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        return $this->plans->findOrFail($id);
    }

    public function cachedPlanFeatures(SubscriptionPlan $plan): Collection
    {
        return $this->cache->remember(
            'subscription-plan-features',
            null,
            ['plan', $plan->id],
            now()->addMinutes(30),
            function () use ($plan): Collection {
                $plan->loadMissing('planFeatures');

                return $plan->planFeatures->values();
            }
        );
    }

    public function deletePlan(SubscriptionPlan $plan, $performedBy = null, ?string $ipAddress = null): void
    {
        DB::transaction(function () use ($plan, $performedBy, $ipAddress): void {
            $oldValues = $plan->toArray();
            $this->plans->delete($plan);
            $this->invalidator->subscriptionPlanFeatures($plan->id);
            $this->audit->log('plan.deleted', null, 'Subscription plan deleted.', $oldValues, [], $performedBy, $ipAddress);
        });
    }

    public function createPlan(array $attributes, $performedBy = null, ?string $ipAddress = null): SubscriptionPlan
    {
        return DB::transaction(function () use ($attributes, $performedBy, $ipAddress): SubscriptionPlan {
            $features = Arr::pull($attributes, 'features', []);
            $plan = $this->plans->create($attributes);

            if ($features !== []) {
                $this->syncFeatures($plan, $features);
            }

            $this->invalidator->subscriptionPlanFeatures($plan->id);
            $this->audit->log('plan.created', null, 'Subscription plan created.', [], $plan->toArray(), $performedBy, $ipAddress);

            return $this->plans->findOrFail($plan->id);
        });
    }

    public function updatePlan(SubscriptionPlan $plan, array $attributes, $performedBy = null, ?string $ipAddress = null): SubscriptionPlan
    {
        return DB::transaction(function () use ($plan, $attributes, $performedBy, $ipAddress): SubscriptionPlan {
            $oldValues = $plan->toArray();
            $features = Arr::pull($attributes, 'features', null);

            $plan = $this->plans->update($plan, $attributes);

            if (is_array($features)) {
                $this->syncFeatures($plan, $features);
            }

            $this->invalidator->subscriptionPlanFeatures($plan->id);
            $this->audit->log('plan.updated', null, 'Subscription plan updated.', $oldValues, $plan->toArray(), $performedBy, $ipAddress);

            return $this->plans->findOrFail($plan->id);
        });
    }

    public function clonePlan(SubscriptionPlan $sourcePlan, array $attributes, $performedBy = null, ?string $ipAddress = null): SubscriptionPlan
    {
        return DB::transaction(function () use ($sourcePlan, $attributes, $performedBy, $ipAddress): SubscriptionPlan {
            $cloned = $this->plans->create([
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'description' => $attributes['description'] ?? $sourcePlan->description,
                'price_monthly' => $attributes['price_monthly'] ?? $sourcePlan->price_monthly,
                'price_yearly' => $attributes['price_yearly'] ?? $sourcePlan->price_yearly,
                'currency' => $attributes['currency'] ?? $sourcePlan->currency,
                'max_students' => $attributes['max_students'] ?? $sourcePlan->max_students,
                'max_staff' => $attributes['max_staff'] ?? $sourcePlan->max_staff,
                'max_storage_mb' => $attributes['max_storage_mb'] ?? $sourcePlan->max_storage_mb,
                'status' => $attributes['status'] ?? $sourcePlan->status,
            ]);

            $sourcePlan->loadMissing('planFeatures');
            $this->syncFeatures($cloned, $sourcePlan->planFeatures->map(fn ($feature) => [
                'feature_code' => $feature->feature_code,
                'feature_name' => $feature->feature_name,
                'module' => $feature->module,
                'is_enabled' => $feature->is_enabled,
                'limit_value' => $feature->limit_value,
            ])->all());

            $this->invalidator->subscriptionPlanFeatures($cloned->id);
            $this->audit->log('plan.cloned', null, 'Subscription plan cloned.', $sourcePlan->toArray(), $cloned->toArray(), $performedBy, $ipAddress);

            return $this->plans->findOrFail($cloned->id);
        });
    }

    public function managePlanFeatures(SubscriptionPlan $plan, array $features): SubscriptionPlan
    {
        return DB::transaction(function () use ($plan, $features): SubscriptionPlan {
            $this->syncFeatures($plan, $features);
            $this->invalidator->subscriptionPlanFeatures($plan->id);

            return $this->plans->findOrFail($plan->id);
        });
    }

    protected function syncFeatures(SubscriptionPlan $plan, array $features): void
    {
        $plan->loadMissing('planFeatures');
        $featureRows = collect($features);

        $duplicates = $featureRows->pluck('feature_code')->duplicates()->values();
        if ($duplicates->isNotEmpty()) {
            throw ValidationException::withMessages([
                'features' => ['Duplicate feature codes are not allowed: '.$duplicates->implode(', ')],
            ]);
        }

        $plan->planFeatures()->delete();
        $plan->planFeatures()->createMany($featureRows->map(function (array $feature): array {
            return [
                'feature_code' => $feature['feature_code'],
                'feature_name' => $feature['feature_name'],
                'module' => $feature['module'],
                'is_enabled' => (bool) ($feature['is_enabled'] ?? true),
                'limit_value' => $feature['limit_value'] ?? null,
            ];
        })->all());
    }
}
