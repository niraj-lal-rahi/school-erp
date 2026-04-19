<?php

namespace Tests\Feature\SIS;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function headers(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_user_can_enroll_student_and_current_enrollment_is_switched(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $user->school_id)->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/students/'.$student->id.'/enroll', [
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'roll_number' => '10A-99',
            'enrollment_date' => '2026-07-01',
            'joined_on' => '2026-07-01',
            'status' => 'enrolled',
            'is_current' => true,
        ])->assertCreated()->assertJsonPath('data.roll_number', '10A-99');

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'roll_number' => '10A-99',
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'roll_number' => '10A-01',
            'is_current' => false,
        ]);
    }
}
