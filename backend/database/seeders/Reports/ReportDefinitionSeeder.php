<?php

namespace Database\Seeders\Reports;

use App\Models\Reports\ReportDefinition;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->first();

        $definitions = [
            [
                'name' => 'Attendance Summary',
                'code' => 'ATTENDANCE-SUMMARY',
                'module' => 'attendance',
                'description' => 'School-wide attendance summary with student, class, and staff insights.',
                'query_config' => [
                    'metrics' => ['student_summary', 'class_summaries', 'staff_summary'],
                    'group_by' => ['class_id', 'section_id'],
                ],
                'default_filters' => [
                    'date_from' => now()->startOfMonth()->toDateString(),
                    'date_to' => now()->toDateString(),
                ],
            ],
            [
                'name' => 'Fee Summary',
                'code' => 'FEE-SUMMARY',
                'module' => 'finance',
                'description' => 'Collection, outstanding dues, and payment method split.',
                'query_config' => [
                    'metrics' => ['daily_collection', 'monthly_collection', 'outstanding_dues', 'payment_method_split'],
                ],
                'default_filters' => [
                    'date_from' => now()->startOfMonth()->toDateString(),
                    'date_to' => now()->toDateString(),
                ],
            ],
            [
                'name' => 'Exam Summary',
                'code' => 'EXAM-SUMMARY',
                'module' => 'exams',
                'description' => 'Subject averages, pass/fail ratios, toppers, and trends.',
                'query_config' => [
                    'metrics' => ['subject_averages', 'pass_fail_ratios', 'toppers', 'trends'],
                ],
                'default_filters' => [],
            ],
        ];

        foreach ($definitions as $definition) {
            ReportDefinition::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $definition['code'],
                ],
                [
                    'name' => $definition['name'],
                    'module' => $definition['module'],
                    'description' => $definition['description'],
                    'query_config' => $definition['query_config'],
                    'default_filters' => $definition['default_filters'],
                    'is_system' => true,
                    'status' => 'active',
                    'created_by' => $admin?->id,
                ],
            );
        }
    }
}
