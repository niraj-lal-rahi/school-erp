<?php

namespace Database\Seeders\Examination;

use App\Models\Examination\ExamType;
use App\Models\School;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $types = [
            ['name' => 'Unit Test', 'code' => 'UNIT', 'description' => 'Short cycle formative assessment.'],
            ['name' => 'Term Exam', 'code' => 'TERM', 'description' => 'Term-end examination.'],
            ['name' => 'Final Exam', 'code' => 'FINAL', 'description' => 'Comprehensive final examination.'],
        ];

        foreach ($types as $type) {
            ExamType::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $type['code'],
                ],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
