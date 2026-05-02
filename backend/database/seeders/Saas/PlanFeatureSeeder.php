<?php

namespace Database\Seeders\Saas;

use App\Models\Saas\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $featuresByPlan = [
            'basic' => [
                ['feature_code' => 'academic', 'feature_name' => 'Academic Management', 'module' => 'academic', 'is_enabled' => true],
                ['feature_code' => 'students', 'feature_name' => 'Student Information System', 'module' => 'students', 'is_enabled' => true],
                ['feature_code' => 'attendance', 'feature_name' => 'Attendance', 'module' => 'attendance', 'is_enabled' => true],
                ['feature_code' => 'fees', 'feature_name' => 'Fees & Finance', 'module' => 'fees', 'is_enabled' => true],
                ['feature_code' => 'communication', 'feature_name' => 'Communication', 'module' => 'communication', 'is_enabled' => true],
            ],
            'pro' => [
                ['feature_code' => 'academic', 'feature_name' => 'Academic Management', 'module' => 'academic', 'is_enabled' => true],
                ['feature_code' => 'students', 'feature_name' => 'Student Information System', 'module' => 'students', 'is_enabled' => true],
                ['feature_code' => 'attendance', 'feature_name' => 'Attendance', 'module' => 'attendance', 'is_enabled' => true],
                ['feature_code' => 'fees', 'feature_name' => 'Fees & Finance', 'module' => 'fees', 'is_enabled' => true],
                ['feature_code' => 'communication', 'feature_name' => 'Communication', 'module' => 'communication', 'is_enabled' => true],
                ['feature_code' => 'timetable', 'feature_name' => 'Timetable', 'module' => 'timetable', 'is_enabled' => true],
                ['feature_code' => 'transport', 'feature_name' => 'Transport', 'module' => 'transport', 'is_enabled' => true],
                ['feature_code' => 'exams', 'feature_name' => 'Examinations', 'module' => 'exams', 'is_enabled' => true],
                ['feature_code' => 'reports', 'feature_name' => 'Reports', 'module' => 'reports', 'is_enabled' => true],
                ['feature_code' => 'portal', 'feature_name' => 'Parent Portal', 'module' => 'portal', 'is_enabled' => true],
            ],
            'enterprise' => [
                ['feature_code' => 'academic', 'feature_name' => 'Academic Management', 'module' => 'academic', 'is_enabled' => true],
                ['feature_code' => 'students', 'feature_name' => 'Student Information System', 'module' => 'students', 'is_enabled' => true],
                ['feature_code' => 'attendance', 'feature_name' => 'Attendance', 'module' => 'attendance', 'is_enabled' => true],
                ['feature_code' => 'fees', 'feature_name' => 'Fees & Finance', 'module' => 'fees', 'is_enabled' => true],
                ['feature_code' => 'communication', 'feature_name' => 'Communication', 'module' => 'communication', 'is_enabled' => true],
                ['feature_code' => 'timetable', 'feature_name' => 'Timetable', 'module' => 'timetable', 'is_enabled' => true],
                ['feature_code' => 'transport', 'feature_name' => 'Transport', 'module' => 'transport', 'is_enabled' => true],
                ['feature_code' => 'exams', 'feature_name' => 'Examinations', 'module' => 'exams', 'is_enabled' => true],
                ['feature_code' => 'reports', 'feature_name' => 'Reports', 'module' => 'reports', 'is_enabled' => true],
                ['feature_code' => 'portal', 'feature_name' => 'Parent Portal', 'module' => 'portal', 'is_enabled' => true],
                ['feature_code' => 'rbac', 'feature_name' => 'RBAC', 'module' => 'rbac', 'is_enabled' => true],
                ['feature_code' => 'priority_support', 'feature_name' => 'Priority Support', 'module' => 'support', 'is_enabled' => true],
            ],
        ];

        foreach ($featuresByPlan as $planCode => $features) {
            $plan = SubscriptionPlan::query()->where('code', $planCode)->first();

            if (! $plan) {
                continue;
            }

            $plan->planFeatures()->delete();
            $plan->planFeatures()->createMany($features);
        }
    }
}
