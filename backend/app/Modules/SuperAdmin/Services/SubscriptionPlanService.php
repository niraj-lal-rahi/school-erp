<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlanFeature;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\SubscriptionPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionPlanService
{
    protected string $platformConnection = 'platform';

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SubscriptionPlan::query()
            ->withCount(['features', 'subscriptions'])
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['search']), function ($query) use ($filters): void {
                $search = trim((string) $filters['search']);

                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        return SubscriptionPlan::query()->with(['features'])->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?int $performedByUserId = null, ?string $ipAddress = null, ?string $userAgent = null): SubscriptionPlan
    {
        return DB::connection($this->platformConnection)->transaction(function () use ($attributes, $performedByUserId, $ipAddress, $userAgent): SubscriptionPlan {
            $plan = SubscriptionPlan::query()->create([
                'name' => $attributes['name'],
                'code' => $attributes['code'] ?? Str::slug((string) $attributes['name']),
                'description' => $attributes['description'] ?? null,
                'price_monthly' => $attributes['price_monthly'] ?? 0,
                'price_yearly' => $attributes['price_yearly'] ?? null,
                'currency' => $attributes['currency'] ?? 'INR',
                'max_students' => $attributes['max_students'] ?? null,
                'max_staff' => $attributes['max_staff'] ?? null,
                'max_storage_mb' => $attributes['max_storage_mb'] ?? null,
                'status' => $attributes['status'] ?? 'active',
            ]);

            $this->logAction('subscription_plan_created', 'subscription_plan', 'Subscription plan created.', [
                'plan_id' => $plan->id,
                'code' => $plan->code,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $plan->loadCount(['features', 'subscriptions']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SubscriptionPlan $plan, array $attributes, ?int $performedByUserId = null, ?string $ipAddress = null, ?string $userAgent = null): SubscriptionPlan
    {
        return DB::connection($this->platformConnection)->transaction(function () use ($plan, $attributes, $performedByUserId, $ipAddress, $userAgent): SubscriptionPlan {
            $plan->fill($attributes);
            $plan->save();

            $this->logAction('subscription_plan_updated', 'subscription_plan', 'Subscription plan updated.', [
                'plan_id' => $plan->id,
                'changes' => $attributes,
            ], $performedByUserId, $ipAddress, $userAgent);

            return $plan->loadCount(['features', 'subscriptions']);
        });
    }

    public function delete(SubscriptionPlan $plan, ?int $performedByUserId = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::connection($this->platformConnection)->transaction(function () use ($plan, $performedByUserId, $ipAddress, $userAgent): void {
            $plan->delete();

            $this->logAction('subscription_plan_deleted', 'subscription_plan', 'Subscription plan deleted.', [
                'plan_id' => $plan->id,
                'code' => $plan->code,
            ], $performedByUserId, $ipAddress, $userAgent);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $features
     */
    public function syncFeatures(SubscriptionPlan $plan, array $features, ?int $performedByUserId = null, ?string $ipAddress = null, ?string $userAgent = null): SubscriptionPlan
    {
        return DB::connection($this->platformConnection)->transaction(function () use ($plan, $features, $performedByUserId, $ipAddress, $userAgent): SubscriptionPlan {
            $incomingCodes = collect($features)
                ->pluck('feature_code')
                ->filter()
                ->values()
                ->all();

            if ($incomingCodes !== []) {
                PlanFeature::query()
                    ->where('subscription_plan_id', $plan->id)
                    ->whereNotIn('feature_code', $incomingCodes)
                    ->delete();
            } else {
                PlanFeature::query()
                    ->where('subscription_plan_id', $plan->id)
                    ->delete();
            }

            foreach ($features as $feature) {
                PlanFeature::query()->updateOrCreate(
                    [
                        'subscription_plan_id' => $plan->id,
                        'feature_code' => $feature['feature_code'],
                    ],
                    [
                        'feature_name' => $feature['feature_name'],
                        'module' => $feature['module'],
                        'is_enabled' => (bool) ($feature['is_enabled'] ?? true),
                        'limit_value' => $feature['limit_value'] ?? null,
                        'config' => $feature['config'] ?? null,
                    ]
                );
            }

            $this->logAction('subscription_plan_features_synced', 'subscription_plan', 'Subscription plan feature matrix updated.', [
                'plan_id' => $plan->id,
                'feature_count' => count($features),
            ], $performedByUserId, $ipAddress, $userAgent);

            return $plan->fresh(['features'])->loadCount(['features', 'subscriptions']);
        });
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => null,
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
