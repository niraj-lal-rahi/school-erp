<?php

namespace App\Services\Saas;

use App\Models\HR\Staff;
use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Models\Saas\TenantUsageLimit;
use App\Models\Student;
use App\Repositories\Contracts\Saas\TenantUsageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantUsageService
{
    public function __construct(
        protected TenantUsageRepositoryInterface $usage,
        protected TenantAuditService $audit,
    ) {
    }

    public function getUsage(Tenant $tenant): TenantUsageLimit
    {
        return $this->usage->findByTenantOrFail($tenant->id);
    }

    public function calculateUsage(Tenant $tenant): array
    {
        $studentCount = Student::withoutGlobalScopes()->where('school_id', $tenant->id)->count();
        $staffCount = Staff::withoutGlobalScopes()->where('school_id', $tenant->id)->count();
        $storageMb = $this->calculateStorageMb($tenant);

        return [
            'current_students' => $studentCount,
            'current_staff' => $staffCount,
            'current_storage_mb' => $storageMb,
        ];
    }

    public function enforceUsageLimit(Tenant $tenant, string $limitType): void
    {
        $usage = $this->usage->findByTenantOrFail($tenant->id);
        $map = [
            'students' => ['current_students', 'max_students'],
            'staff' => ['current_staff', 'max_staff'],
            'storage' => ['current_storage_mb', 'max_storage_mb'],
        ];

        if (! isset($map[$limitType])) {
            return;
        }

        [$currentKey, $maxKey] = $map[$limitType];
        if ($usage->{$maxKey} !== null && $usage->{$currentKey} > $usage->{$maxKey}) {
            throw ValidationException::withMessages([
                $limitType => ["The tenant has exceeded the allowed {$limitType} limit."],
            ]);
        }
    }

    public function isLimitExceeded(Tenant $tenant, string $limitType): bool
    {
        $usage = $this->usage->findByTenantOrFail($tenant->id);

        return match ($limitType) {
            'students' => $usage->max_students !== null && $usage->current_students > $usage->max_students,
            'staff' => $usage->max_staff !== null && $usage->current_staff > $usage->max_staff,
            'storage' => $usage->max_storage_mb !== null && $usage->current_storage_mb > $usage->max_storage_mb,
            default => false,
        };
    }

    public function syncUsageCounters(Tenant $tenant, ?SubscriptionPlan $plan = null, $performedBy = null, ?string $ipAddress = null): TenantUsageLimit
    {
        return DB::transaction(function () use ($tenant, $plan, $performedBy, $ipAddress): TenantUsageLimit {
            $usageData = $this->calculateUsage($tenant);
            $activeSubscription = $tenant->activeSubscription()->with('subscriptionPlan')->first();
            $plan ??= $activeSubscription?->subscriptionPlan;

            $usage = $this->usage->updateOrCreateForTenant($tenant->id, array_merge([
                'subscription_plan_id' => $plan?->id,
                'max_students' => $plan?->max_students,
                'max_staff' => $plan?->max_staff,
                'max_storage_mb' => $plan?->max_storage_mb,
            ], $usageData));

            $this->audit->log('tenant.usage.synced', $tenant->id, 'Tenant usage counters synchronized.', [], $usage->toArray(), $performedBy, $ipAddress);

            return $usage;
        });
    }

    protected function calculateStorageMb(Tenant $tenant): int
    {
        $settings = (array) ($tenant->settings ?? []);
        $configuredMb = $settings['storage_usage_mb'] ?? 0;

        return (int) ceil((float) $configuredMb);
    }
}
