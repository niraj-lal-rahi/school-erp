<?php

namespace Tests\Feature\Saas;

use App\Models\Role;
use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Models\Saas\TenantBillingRecord;
use App\Models\User;
use App\Services\Saas\TenantDomainService;
use App\Services\Saas\TenantFeatureService;
use App\Services\Saas\TenantUsageService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaasApiTest extends TestCase
{
    use RefreshDatabase;

    protected function superAdminHeaders(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'superadmin@system.local')->firstOrFail();
        $role = Role::withoutGlobalScopes()->where('code', 'super_admin')->firstOrFail();
        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => null],
        ]);

        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    protected function tenantAdminHeaders(string $email = 'admin@greenwood.edu', string $tenantCode = 'greenwood'): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => $tenantCode,
        ];
    }

    public function test_super_admin_can_create_tenant(): void
    {
        $headers = $this->superAdminHeaders();

        $response = $this->withHeaders($headers)->postJson('/api/v1/saas/tenants', [
            'name' => 'Aurora School',
            'code' => 'aurora',
            'email' => 'hello@aurora.edu',
            'phone' => '9111111111',
            'domain' => 'aurora.local',
            'subdomain' => 'aurora',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'trial',
        ])->assertCreated();

        $this->assertDatabaseHas('schools', [
            'id' => $response->json('data.id'),
            'code' => 'aurora',
            'status' => 'trial',
        ]);
    }

    public function test_super_admin_can_onboard_tenant_and_create_admin(): void
    {
        $headers = $this->superAdminHeaders();
        $plan = SubscriptionPlan::query()->where('code', 'basic')->firstOrFail();

        $response = $this->withHeaders($headers)->postJson('/api/v1/saas/onboard-school', [
            'tenant' => [
                'name' => 'North Valley School',
                'code' => 'north-valley',
                'email' => 'info@northvalley.edu',
                'domain' => 'northvalley.local',
                'subdomain' => 'northvalley',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
            ],
            'admin' => [
                'first_name' => 'North',
                'last_name' => 'Admin',
                'email' => 'admin@northvalley.edu',
                'phone' => '9222222222',
                'password' => 'password123',
            ],
            'plan_id' => $plan->id,
            'trial_days' => 21,
        ])->assertCreated();

        $tenantId = $response->json('data.tenant.id');
        $adminUserId = $response->json('data.admin_user.id');

        $this->assertDatabaseHas('schools', [
            'id' => $tenantId,
            'code' => 'north-valley',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $adminUserId,
            'school_id' => $tenantId,
            'email' => 'admin@northvalley.edu',
        ]);

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $adminUserId,
            'school_id' => $tenantId,
        ]);
    }

    public function test_super_admin_can_subscribe_tenant_to_plan(): void
    {
        $headers = $this->superAdminHeaders();
        $tenant = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $plan = SubscriptionPlan::query()->where('code', 'enterprise')->firstOrFail();

        $response = $this->withHeaders($headers)->postJson("/api/v1/saas/tenants/{$tenant->id}/subscribe", [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'auto_renew' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $response->json('data.id'),
            'school_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
        ]);
    }

    public function test_super_admin_can_change_tenant_plan(): void
    {
        $headers = $this->superAdminHeaders();
        $tenant = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $plan = SubscriptionPlan::query()->where('code', 'basic')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/saas/tenants/{$tenant->id}/change-plan", [
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.subscription_plan_id', $plan->id);
    }

    public function test_feature_check_reflects_tenant_plan_access(): void
    {
        $this->seed();

        $tenant = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $features = app(TenantFeatureService::class);

        $this->assertTrue($features->checkFeatureAccess($tenant, 'transport'));
        $this->assertFalse($features->checkFeatureAccess($tenant, 'priority_support'));
    }

    public function test_usage_limit_check_detects_exceeded_limit(): void
    {
        $this->seed();

        $tenant = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $tenant->tenantUsageLimit()->update([
            'max_students' => 10,
            'current_students' => 11,
        ]);

        $usage = app(TenantUsageService::class);

        $this->assertTrue($usage->isLimitExceeded($tenant, 'students'));
    }

    public function test_suspended_tenant_is_blocked_from_erp_routes(): void
    {
        $headers = $this->tenantAdminHeaders();

        $tenant = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $tenant->update(['status' => 'suspended', 'suspended_at' => now()]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/dashboard/overview')
            ->assertForbidden();
    }

    public function test_domain_resolution_service_returns_correct_tenant(): void
    {
        $this->seed();

        $tenant = app(TenantDomainService::class)->resolveTenantByDomain('greenwood.local');

        $this->assertNotNull($tenant);
        $this->assertSame('greenwood', $tenant->code);
    }

    public function test_tenant_admin_can_view_own_tenant_but_not_another_tenant(): void
    {
        $headers = $this->tenantAdminHeaders();
        $greenwood = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $riverside = Tenant::withoutGlobalScopes()->where('code', 'riverside')->firstOrFail();

        $this->withHeaders($headers)->getJson("/api/v1/saas/tenants/{$greenwood->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'greenwood');

        $this->withHeaders($headers)->getJson("/api/v1/saas/tenants/{$riverside->id}")
            ->assertForbidden();
    }

    public function test_super_admin_can_mark_billing_record_paid(): void
    {
        $headers = $this->superAdminHeaders();
        $record = TenantBillingRecord::query()->where('status', 'pending')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/saas/billing/{$record->id}/mark-paid")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');
    }
}
