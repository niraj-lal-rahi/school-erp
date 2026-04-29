<?php

namespace Database\Seeders\Examination;

use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use App\Models\School;
use Illuminate\Database\Seeder;

class GradingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $gradingSystem = GradingSystem::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'STD-PERCENT',
            ],
            [
                'name' => 'Standard Percentage System',
                'grading_type' => 'percentage',
                'pass_percentage' => 35,
                'description' => 'Default percentage-based grading system for examinations.',
                'status' => 'active',
            ]
        );

        $scales = [
            ['grade_label' => 'A+', 'min_percentage' => 90, 'max_percentage' => 100, 'grade_point' => 4.0, 'remarks' => 'Outstanding'],
            ['grade_label' => 'A', 'min_percentage' => 80, 'max_percentage' => 89.99, 'grade_point' => 3.7, 'remarks' => 'Excellent'],
            ['grade_label' => 'B', 'min_percentage' => 70, 'max_percentage' => 79.99, 'grade_point' => 3.0, 'remarks' => 'Very Good'],
            ['grade_label' => 'C', 'min_percentage' => 50, 'max_percentage' => 69.99, 'grade_point' => 2.0, 'remarks' => 'Satisfactory'],
            ['grade_label' => 'D', 'min_percentage' => 35, 'max_percentage' => 49.99, 'grade_point' => 1.0, 'remarks' => 'Pass'],
            ['grade_label' => 'F', 'min_percentage' => 0, 'max_percentage' => 34.99, 'grade_point' => 0.0, 'remarks' => 'Fail'],
        ];

        foreach ($scales as $scale) {
            GradeScale::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'grading_system_id' => $gradingSystem->id,
                    'grade_label' => $scale['grade_label'],
                ],
                $scale
            );
        }
    }
}
