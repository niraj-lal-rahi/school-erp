<?php

namespace Tests\Feature\Attendance;

use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceHoliday;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionsHolidaysApiTest extends TestCase
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

    public function test_student_attendance_session_cannot_be_created_on_student_holiday(): void
    {
        $headers = $this->authenticate();
        $enrollment = StudentEnrollment::withoutGlobalScopes()->where('is_current', true)->firstOrFail();

        AttendanceHoliday::withoutGlobalScopes()->create([
            'school_id' => $enrollment->school_id,
            'academic_year_id' => $enrollment->academic_year_id,
            'title' => 'Section Holiday',
            'description' => 'Holiday for test class scope.',
            'start_date' => '2026-05-02',
            'end_date' => '2026-05-02',
            'applies_to' => 'students',
            'school_class_id' => $enrollment->school_class_id,
            'section_id' => $enrollment->section_id,
            'is_recurring' => false,
        ]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/student-sessions', [
                'academic_year_id' => $enrollment->academic_year_id,
                'school_class_id' => $enrollment->school_class_id,
                'section_id' => $enrollment->section_id,
                'attendance_date' => '2026-05-02',
                'session_type' => 'daily',
            ])
            ->assertStatus(422);
    }

    public function test_staff_attendance_cannot_be_marked_on_staff_holiday(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();
        $presentStatus = AttendanceStatusType::withoutGlobalScopes()->where('school_id', $staff->school_id)->where('code', 'PRESENT')->firstOrFail();
        $academicYearId = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $staff->school_id)
            ->where('is_current', true)
            ->value('id');

        AttendanceHoliday::withoutGlobalScopes()->create([
            'school_id' => $staff->school_id,
            'academic_year_id' => $academicYearId,
            'title' => 'Staff Holiday',
            'description' => 'Holiday for staff attendance test.',
            'start_date' => '2026-05-03',
            'end_date' => '2026-05-03',
            'applies_to' => 'staff',
            'school_class_id' => null,
            'section_id' => null,
            'is_recurring' => false,
        ]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/staff/'.$staff->id.'/mark', [
                'attendance_date' => '2026-05-03',
                'attendance_status_type_id' => $presentStatus->id,
                'source' => 'manual',
            ])
            ->assertStatus(422);
    }

    public function test_approved_staff_attendance_correction_updates_record(): void
    {
        $headers = $this->authenticate();
        $staffAttendance = StaffAttendance::withoutGlobalScopes()->firstOrFail();
        $absentStatus = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $staffAttendance->school_id)
            ->where('code', 'ABSENT')
            ->firstOrFail();

        $correctionResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/corrections', [
                'reference_type' => 'staff',
                'reference_id' => $staffAttendance->staff_id,
                'attendance_date' => $staffAttendance->attendance_date?->toDateString(),
                'new_status_id' => $absentStatus->id,
                'reason' => 'Manual correction from test.',
            ])
            ->assertCreated();

        $correctionId = $correctionResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/corrections/{$correctionId}/approve", [
                'review_remarks' => 'Approved in feature test.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('staff_attendance', [
            'id' => $staffAttendance->id,
            'attendance_status_type_id' => $absentStatus->id,
            'attendance_status' => 'absent',
        ]);
    }

    public function test_approved_student_attendance_correction_updates_records_for_date(): void
    {
        $headers = $this->authenticate();
        $enrollment = StudentEnrollment::withoutGlobalScopes()->where('is_current', true)->firstOrFail();
        $presentStatus = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $enrollment->school_id)
            ->where('code', 'PRESENT')
            ->firstOrFail();
        $sessionResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/student-sessions', [
                'academic_year_id' => $enrollment->academic_year_id,
                'school_class_id' => $enrollment->school_class_id,
                'section_id' => $enrollment->section_id,
                'attendance_date' => '2026-05-04',
                'session_type' => 'daily',
            ])
            ->assertCreated();
        $sessionId = $sessionResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/student-sessions/{$sessionId}/bulk-mark", [
                'records' => [[
                    'student_id' => $enrollment->student_id,
                    'attendance_status_type_id' => $presentStatus->id,
                    'remarks' => 'Seeded inside correction test.',
                ]],
            ])
            ->assertOk();

        $session = StudentAttendanceSession::withoutGlobalScopes()->findOrFail($sessionId);
        $lateStatus = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $session->school_id)
            ->where('code', 'LATE')
            ->firstOrFail();

        $correctionResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/corrections', [
                'reference_type' => 'student',
                'reference_id' => $enrollment->student_id,
                'attendance_date' => $session->attendance_date?->toDateString(),
                'new_status_id' => $lateStatus->id,
                'reason' => 'Student correction from test.',
            ])
            ->assertCreated();

        $correctionId = $correctionResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/corrections/{$correctionId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('attendance_student_records', [
            'attendance_session_id' => $sessionId,
            'student_id' => $enrollment->student_id,
            'attendance_status_type_id' => $lateStatus->id,
        ]);
    }
}
