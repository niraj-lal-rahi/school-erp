<?php

namespace Database\Seeders\SIS;

use App\Models\AcademicYear;
use App\Models\Admission;
use App\Models\Guardian;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SisSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $school->id)->where('is_current', true)->firstOrFail();
        $user = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $class = SchoolClass::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'code' => 'G10',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Grade 10',
                'grade_level' => 10,
                'sort_order' => 10,
            ]
        );

        $section = Section::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'school_class_id' => $class->id,
                'name' => 'A',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'capacity' => 35,
                'class_teacher_id' => $user->id,
            ]
        );

        $guardian = Guardian::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'parent1@example.com',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Asha',
                'last_name' => 'Sharma',
                'phone' => '8888888888',
                'relationship_type' => 'Mother',
                'occupation' => 'Architect',
                'address' => [
                    'line1' => '12 MG Road',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'postal_code' => '560001',
                ],
            ]
        );

        $student = Student::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'admission_no' => 'ADM-2026-0001',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riya',
                'last_name' => 'Sharma',
                'preferred_name' => 'Riya',
                'email' => 'riya.sharma@student.greenwood.edu',
                'phone' => '7777777777',
                'gender' => 'female',
                'date_of_birth' => '2011-08-14',
                'admission_date' => '2026-04-10',
                'blood_group' => 'B+',
                'status' => 'active',
                'address' => [
                    'line1' => '12 MG Road',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'postal_code' => '560001',
                ],
                'medical_notes' => 'Peanut allergy',
            ]
        );

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'school_id' => $school->id,
                'relationship' => 'Mother',
                'is_primary' => true,
                'is_emergency_contact' => true,
                'pickup_authorized' => true,
            ],
        ]);

        Admission::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
            ],
            [
                'academic_year_id' => $academicYear->id,
                'applied_class_id' => $class->id,
                'status' => 'admitted',
                'applied_on' => '2026-04-01',
                'admitted_on' => '2026-04-10',
                'remarks' => 'Scholarship review completed.',
            ]
        );

        StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-01',
                'status' => 'active',
                'joined_on' => '2026-04-10',
            ]
        );
    }
}
