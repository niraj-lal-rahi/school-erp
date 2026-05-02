<?php

namespace Database\Seeders\Saas;

use App\Models\Saas\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'code' => 'basic',
                'description' => 'Entry plan for smaller schools.',
                'price_monthly' => 1999.00,
                'price_yearly' => 19999.00,
                'currency' => 'INR',
                'max_students' => 300,
                'max_staff' => 30,
                'max_storage_mb' => 5120,
                'status' => 'active',
            ],
            [
                'name' => 'Pro',
                'code' => 'pro',
                'description' => 'Growth plan for mid-sized schools.',
                'price_monthly' => 4999.00,
                'price_yearly' => 49999.00,
                'currency' => 'INR',
                'max_students' => 1000,
                'max_staff' => 100,
                'max_storage_mb' => 20480,
                'status' => 'active',
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'description' => 'Unlimited plan with priority support.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'currency' => 'INR',
                'max_students' => null,
                'max_staff' => null,
                'max_storage_mb' => null,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
