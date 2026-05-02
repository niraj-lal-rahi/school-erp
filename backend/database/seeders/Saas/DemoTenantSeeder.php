<?php

namespace Database\Seeders\Saas;

use App\Models\Saas\SubscriptionPlan;
use App\Models\Saas\Tenant;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $basic = SubscriptionPlan::query()->where('code', 'basic')->firstOrFail();
        $pro = SubscriptionPlan::query()->where('code', 'pro')->firstOrFail();
        $enterprise = SubscriptionPlan::query()->where('code', 'enterprise')->firstOrFail();

        $greenwood = Tenant::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $greenwood->update([
            'email' => 'info@greenwood.edu',
            'phone' => '9999999999',
            'subdomain' => 'greenwood',
            'city' => 'Kolkata',
            'state' => 'West Bengal',
            'country' => 'India',
            'postal_code' => '700001',
            'currency' => 'INR',
            'status' => 'active',
            'activated_at' => now()->subMonths(3),
            'trial_ends_at' => now()->subMonths(2),
            'settings' => array_merge((array) ($greenwood->settings ?? []), [
                'storage_usage_mb' => 256,
            ]),
        ]);

        $this->seedTenantSubscriptionGraph($greenwood, $pro, 'active');

        $riverside = Tenant::withoutGlobalScopes()->updateOrCreate(
            ['code' => 'riverside'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Riverside Public School',
                'slug' => 'riverside-public-school',
                'email' => 'contact@riverside.edu',
                'phone' => '9000000001',
                'domain' => 'riverside.local',
                'subdomain' => 'riverside',
                'timezone' => 'Asia/Calcutta',
                'currency' => 'INR',
                'locale' => 'en',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(10),
                'settings' => ['storage_usage_mb' => 64],
                'storage_disk' => 's3',
            ]
        );

        $this->seedTenantSubscriptionGraph($riverside, $basic, 'trial');

        $summit = Tenant::withoutGlobalScopes()->updateOrCreate(
            ['code' => 'summit'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Summit International Academy',
                'slug' => 'summit-international-academy',
                'email' => 'hello@summit.edu',
                'phone' => '9000000002',
                'domain' => 'summit.local',
                'subdomain' => 'summit',
                'timezone' => 'Asia/Calcutta',
                'currency' => 'INR',
                'locale' => 'en',
                'status' => 'active',
                'activated_at' => now()->subYear(),
                'settings' => ['storage_usage_mb' => 1024],
                'storage_disk' => 's3',
            ]
        );

        $this->seedTenantSubscriptionGraph($summit, $enterprise, 'active');

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'superadmin@system.local'],
            [
                'uuid' => (string) Str::uuid(),
                'school_id' => null,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'name' => 'Super Admin',
                'phone' => '9000000099',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    protected function seedTenantSubscriptionGraph(Tenant $tenant, SubscriptionPlan $plan, string $status): void
    {
        $subscription = TenantSubscription::query()->updateOrCreate(
            [
                'school_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'status' => $status,
            ],
            [
                'billing_cycle' => 'monthly',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => $status === 'trial' ? now()->addDays(10)->toDateString() : now()->addMonth()->toDateString(),
                'trial_ends_at' => $status === 'trial' ? now()->addDays(10) : null,
                'auto_renew' => true,
            ]
        );

        $tenant->tenantUsageLimit()->updateOrCreate(
            ['school_id' => $tenant->id],
            [
                'subscription_plan_id' => $plan->id,
                'max_students' => $plan->max_students,
                'max_staff' => $plan->max_staff,
                'max_storage_mb' => $plan->max_storage_mb,
                'current_students' => 0,
                'current_staff' => 0,
                'current_storage_mb' => (int) ((array) ($tenant->settings ?? []))['storage_usage_mb'],
            ]
        );

        $tenant->tenantDomains()->updateOrCreate(
            ['domain' => $tenant->domain],
            [
                'domain_type' => 'primary',
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
            ]
        );

        $tenant->tenantDomains()->updateOrCreate(
            ['domain' => $tenant->subdomain],
            [
                'domain_type' => 'subdomain',
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
            ]
        );

        $tenant->tenantFeatureAccesses()->delete();
        foreach ($plan->planFeatures as $feature) {
            $tenant->tenantFeatureAccesses()->create([
                'feature_code' => $feature->feature_code,
                'module' => $feature->module,
                'is_enabled' => $feature->is_enabled,
                'limit_value' => $feature->limit_value,
            ]);
        }

        TenantBillingRecord::query()->updateOrCreate(
            ['invoice_no' => 'INV-'.$tenant->id.'-DEMO'],
            [
                'school_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price_monthly,
                'currency' => $plan->currency,
                'billing_cycle' => 'monthly',
                'billing_date' => now()->subDays(5)->toDateString(),
                'due_date' => now()->addDays(2)->toDateString(),
                'paid_at' => $status === 'active' ? now()->subDays(4) : null,
                'status' => $status === 'active' ? 'paid' : 'pending',
            ]
        );
    }
}
