<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance\AttendanceImport;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\BiometricLog;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceImportsBiometricApiTest extends TestCase
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

    public function test_staff_import_can_be_created_and_processed(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();

        $createResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/imports', [
                'import_type' => 'staff',
                'import_date' => '2026-05-05',
                'rows' => [[
                    'staff_id' => $staff->id,
                    'attendance_date' => '2026-05-05',
                    'attendance_status' => 'PRESENT',
                    'check_in_time' => '08:00',
                    'check_out_time' => '16:00',
                    'source' => 'import',
                ]],
            ])
            ->assertCreated();

        $importId = $createResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/attendance/imports/{$importId}/process")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('staff_attendance', [
            'staff_id' => $staff->id,
            'attendance_date' => '2026-05-05 00:00:00',
            'attendance_status' => 'present',
        ]);
    }

    public function test_student_biometric_log_processing_creates_attendance_record(): void
    {
        $headers = $this->authenticate();
        $enrollment = StudentEnrollment::withoutGlobalScopes()->where('is_current', true)->firstOrFail();
        $student = Student::withoutGlobalScopes()->findOrFail($enrollment->student_id);

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/biometric-logs', [
                'device_id' => 'DEVICE-2',
                'user_type' => 'student',
                'user_id' => $student->id,
                'log_datetime' => '2026-05-06 08:12:00',
                'log_type' => 'check_in',
                'raw_data' => ['source' => 'test'],
            ])
            ->assertCreated();

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/biometric-logs/process', [
                'user_type' => 'student',
            ])
            ->assertOk();

        $presentStatusId = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $student->school_id)
            ->where('code', 'PRESENT')
            ->value('id');

        $this->assertDatabaseHas('attendance_student_records', [
            'student_id' => $student->id,
            'attendance_status_type_id' => $presentStatusId,
        ]);

        $this->assertDatabaseHas('attendance_biometric_logs', [
            'user_type' => 'student',
            'user_id' => $student->id,
            'processed' => true,
        ]);
    }
}
