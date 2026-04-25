<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_list_seeded_attendance_status_types(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/status-types')
            ->assertOk()
            ->assertJsonCount(7, 'data');
    }

    public function test_authorized_user_can_create_session_and_bulk_mark_students(): void
    {
        $headers = $this->authenticate();
        $enrollment = StudentEnrollment::withoutGlobalScopes()->where('is_current', true)->firstOrFail();
        $presentStatusId = AttendanceStatusType::withoutGlobalScopes()->where('code', 'PRESENT')->value('id');

        $sessionResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/student-sessions', [
                'academic_year_id' => $enrollment->academic_year_id,
                'school_class_id' => $enrollment->school_class_id,
                'section_id' => $enrollment->section_id,
                'attendance_date' => now()->addDay()->toDateString(),
                'session_type' => 'daily',
            ])
            ->assertCreated();

        $sessionId = $sessionResponse->json('data.id');

        $studentIds = StudentEnrollment::withoutGlobalScopes()
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->where('school_class_id', $enrollment->school_class_id)
            ->where('section_id', $enrollment->section_id)
            ->where('is_current', true)
            ->limit(2)
            ->pluck('student_id')
            ->all();

        $this->assertNotEmpty($studentIds);

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/student-sessions/{$sessionId}/bulk-mark", [
                'records' => collect($studentIds)->map(fn (int $studentId): array => [
                    'student_id' => $studentId,
                    'attendance_status_type_id' => $presentStatusId,
                    'remarks' => 'Marked by automated test.',
                ])->all(),
            ])
            ->assertOk()
            ->assertJsonCount(count($studentIds), 'data.records');
    }

    public function test_duplicate_daily_session_is_rejected(): void
    {
        $headers = $this->authenticate();
        $session = StudentAttendanceSession::withoutGlobalScopes()->where('session_type', 'daily')->firstOrFail();

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/student-sessions', [
                'academic_year_id' => $session->academic_year_id,
                'school_class_id' => $session->school_class_id,
                'section_id' => $session->section_id,
                'attendance_date' => $session->attendance_date?->toDateString(),
                'session_type' => 'daily',
            ])
            ->assertStatus(422);
    }

    public function test_locked_session_cannot_be_bulk_marked_again(): void
    {
        $headers = $this->authenticate();
        $session = StudentAttendanceSession::withoutGlobalScopes()->where('session_type', 'daily')->firstOrFail();
        $studentId = StudentEnrollment::withoutGlobalScopes()
            ->where('academic_year_id', $session->academic_year_id)
            ->where('school_class_id', $session->school_class_id)
            ->where('section_id', $session->section_id)
            ->where('is_current', true)
            ->value('student_id');
        $absentStatusId = AttendanceStatusType::withoutGlobalScopes()->where('code', 'ABSENT')->value('id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/student-sessions/{$session->id}/lock")
            ->assertOk();

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/student-sessions/{$session->id}/bulk-mark", [
                'records' => [[
                    'student_id' => $studentId,
                    'attendance_status_type_id' => $absentStatusId,
                    'remarks' => 'Should fail after lock.',
                ]],
            ])
            ->assertStatus(422);
    }
}
