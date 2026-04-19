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

class StudentLifecycleApiTest extends TestCase
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

    public function test_user_can_promote_student_and_history_is_recorded(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $user->school_id)->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/students/'.$student->id.'/promote', [
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $class->id,
            'section_id' => $section->id,
            'roll_number' => '10A-22',
            'effective_date' => '2026-06-01',
            'reason' => 'Promoted to next session.',
        ])->assertOk()->assertJsonPath('data.current_status', 'active');

        $this->assertDatabaseHas('student_status_history', [
            'student_id' => $student->id,
            'action_type' => 'promoted',
            'new_status' => 'active',
        ]);

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'roll_number' => '10A-22',
            'is_current' => true,
        ]);
    }

    public function test_user_can_suspend_and_reactivate_student(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/students/'.$student->id.'/suspend', [
            'effective_date' => '2026-06-05',
            'reason' => 'Disciplinary review pending.',
        ])->assertOk()->assertJsonPath('data.current_status', 'suspended');

        $this->withHeaders($headers)->postJson('/api/v1/students/'.$student->id.'/reactivate', [
            'effective_date' => '2026-06-12',
            'reason' => 'Student cleared and allowed back.',
        ])->assertOk()->assertJsonPath('data.current_status', 'active');

        $this->assertDatabaseHas('student_status_history', [
            'student_id' => $student->id,
            'action_type' => 'suspend',
            'new_status' => 'suspended',
        ]);

        $this->assertDatabaseHas('student_status_history', [
            'student_id' => $student->id,
            'action_type' => 'reactivate',
            'new_status' => 'active',
        ]);
    }

    public function test_user_can_add_student_note_and_fetch_history_and_notes(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/students/'.$student->id.'/notes', [
            'note' => 'Parent meeting scheduled for next Monday.',
            'visibility_type' => 'admin_only',
        ])->assertCreated()->assertJsonPath('data.visibility_type', 'admin_only');

        $this->withHeaders($headers)->getJson('/api/v1/students/'.$student->id.'/notes')
            ->assertOk()
            ->assertJsonFragment(['note' => 'Parent meeting scheduled for next Monday.']);

        $this->withHeaders($headers)->getJson('/api/v1/students/'.$student->id.'/status-history')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
