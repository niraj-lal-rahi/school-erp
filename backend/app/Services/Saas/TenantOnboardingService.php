<?php

namespace App\Services\Saas;

use App\Models\Role;
use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Models\User;
use App\Services\Rbac\RoleAssignmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantOnboardingService
{
    public function __construct(
        protected TenantService $tenants,
        protected TenantSubscriptionService $subscriptions,
        protected TenantUsageService $usage,
        protected RoleAssignmentService $roleAssignments,
        protected TenantAuditService $audit,
    ) {
    }

    public function onboardSchool(array $tenantAttributes, array $adminAttributes, ?SubscriptionPlan $plan = null, int $trialDays = 14, $performedBy = null, ?string $ipAddress = null): array
    {
        return DB::transaction(function () use ($tenantAttributes, $adminAttributes, $plan, $trialDays, $performedBy, $ipAddress): array {
            /** @var Tenant $tenant */
            $tenant = $this->tenants->createTenant(array_merge([
                'status' => 'trial',
                'trial_ends_at' => now()->addDays($trialDays),
                'activated_at' => null,
                'suspended_at' => null,
                'locale' => $tenantAttributes['locale'] ?? 'en',
                'timezone' => $tenantAttributes['timezone'] ?? 'Asia/Kolkata',
                'currency' => $tenantAttributes['currency'] ?? 'INR',
                'uuid' => $tenantAttributes['uuid'] ?? (string) Str::uuid(),
                'slug' => $tenantAttributes['slug'] ?? Str::slug($tenantAttributes['name']),
                'storage_disk' => $tenantAttributes['storage_disk'] ?? 's3',
            ], $tenantAttributes), $performedBy, $ipAddress);

            $admin = $this->createDefaultAdminUser($tenant, $adminAttributes);
            $this->assignTenantAdminRole($admin);
            $this->createDefaultSettings($tenant);

            if ($plan) {
                $this->subscriptions->startTrial($tenant, $plan, $trialDays, $performedBy, $ipAddress);
            } else {
                $this->usage->syncUsageCounters($tenant, null, $performedBy, $ipAddress);
            }

            $this->audit->log('tenant.onboarded', $tenant->id, 'Tenant onboarding completed.', [], [
                'tenant_id' => $tenant->id,
                'admin_user_id' => $admin->id,
            ], $performedBy, $ipAddress);

            return [
                'tenant' => $tenant,
                'admin_user' => $admin,
            ];
        });
    }

    protected function createDefaultAdminUser(Tenant $tenant, array $attributes): User
    {
        return User::withoutGlobalScopes()->create([
            'uuid' => $attributes['uuid'] ?? (string) Str::uuid(),
            'school_id' => $tenant->id,
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
            'name' => trim(($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? '')),
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'password' => $attributes['password'],
            'status' => $attributes['status'] ?? 'active',
        ]);
    }

    protected function assignTenantAdminRole(User $admin): void
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($admin->school_id);
        $role = $this->resolveOrCreateTenantAdminRole($tenant);

        $this->roleAssignments->assignRole($admin, $role, $admin);
    }

    protected function resolveOrCreateTenantAdminRole(Tenant $tenant): Role
    {
        $existingRole = Role::withoutGlobalScopes()
            ->where('school_id', $tenant->id)
            ->where(function ($query): void {
                $query->where('code', 'tenant_admin')
                    ->orWhere('slug', 'school-admin');
            })
            ->first();

        if ($existingRole) {
            return $existingRole;
        }

        $templateRole = Role::withoutGlobalScopes()
            ->where('role_type', 'tenant')
            ->where(function ($query): void {
                $query->where('code', 'tenant_admin')
                    ->orWhere('slug', 'school-admin');
            })
            ->with('permissions')
            ->orderByRaw('CASE WHEN is_default = 1 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->first();

        $role = Role::withoutGlobalScopes()->create([
            'school_id' => $tenant->id,
            'uuid' => (string) Str::uuid(),
            'name' => $templateRole?->name ?? 'Tenant Administrator',
            'code' => 'tenant_admin',
            'slug' => 'school-admin',
            'scope' => 'tenant',
            'description' => $templateRole?->description ?? 'Tenant administrator with full school access.',
            'role_type' => 'tenant',
            'is_default' => true,
            'status' => 'active',
        ]);

        if ($templateRole) {
            $permissionIds = $templateRole->permissions->pluck('id')->all();

            $role->permissions()->syncWithPivotValues($permissionIds, [
                'school_id' => $tenant->id,
            ]);
        }

        return $role->load('permissions');
    }

    protected function createDefaultSettings(Tenant $tenant): void
    {
        $settings = (array) ($tenant->settings ?? []);
        $settings = array_merge([
            'saas_onboarded' => true,
            'storage_usage_mb' => 0,
        ], $settings);

        $tenant->update(['settings' => $settings]);
    }
}
