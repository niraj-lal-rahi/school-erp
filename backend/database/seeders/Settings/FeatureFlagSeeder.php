<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['feature_code' => 'academic', 'module' => 'academic', 'name' => 'Academic Module'],
            ['feature_code' => 'students', 'module' => 'students', 'name' => 'Student Information System'],
            ['feature_code' => 'hr', 'module' => 'hr', 'name' => 'HR Module'],
            ['feature_code' => 'finance', 'module' => 'finance', 'name' => 'Finance Module'],
            ['feature_code' => 'attendance', 'module' => 'attendance', 'name' => 'Attendance Module'],
            ['feature_code' => 'timetable', 'module' => 'timetable', 'name' => 'Timetable Module'],
            ['feature_code' => 'transport', 'module' => 'transport', 'name' => 'Transport Module'],
            ['feature_code' => 'communication', 'module' => 'communication', 'name' => 'Communication Module'],
            ['feature_code' => 'exams', 'module' => 'exams', 'name' => 'Exams Module'],
            ['feature_code' => 'reports', 'module' => 'reports', 'name' => 'Reports Module'],
            ['feature_code' => 'portal', 'module' => 'portal', 'name' => 'Parent Portal'],
            ['feature_code' => 'payments', 'module' => 'payments', 'name' => 'Payments Module'],
            ['feature_code' => 'documents', 'module' => 'documents', 'name' => 'Documents Module'],
            ['feature_code' => 'workflow', 'module' => 'workflow', 'name' => 'Workflow Module'],
        ];

        foreach ($features as $feature) {
            FeatureFlag::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => null,
                    'feature_code' => $feature['feature_code'],
                    'module' => $feature['module'],
                ],
                [
                    ...$feature,
                    'description' => $feature['name'].' availability.',
                    'is_enabled' => true,
                    'rollout_percentage' => 100,
                    'config' => [],
                ],
            );
        }

        School::withoutGlobalScopes()->get()->each(function (School $school): void {
            FeatureFlag::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'feature_code' => 'payments',
                    'module' => 'payments',
                ],
                [
                    'name' => 'Payments Module',
                    'description' => 'Tenant override for payments.',
                    'is_enabled' => true,
                    'rollout_percentage' => 100,
                    'config' => ['tenant_override' => true],
                ],
            );
        });
    }
}
