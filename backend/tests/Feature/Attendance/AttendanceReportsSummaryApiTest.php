<?php

namespace Tests\Feature\Attendance;

use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportsSummaryApiTest extends TestCase
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

    public function test_attendance_summary_can_be_refreshed_and_listed(): void
    {
        $headers = $this->authenticate();
        $academicYearId = AcademicYear::withoutGlobalScopes()->where('is_current', true)->value('id');

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/summary/refresh', [
                'academic_year_id' => $academicYearId,
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['user_type', 'user_id', 'academic_year_id', 'total_days', 'present_days', 'percentage'],
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/summary?user_type=student')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['user_type', 'user_id', 'academic_year_id', 'total_days', 'present_days', 'percentage'],
                ],
            ]);
    }

    public function test_attendance_report_endpoints_return_expected_shapes(): void
    {
        $headers = $this->authenticate();
        $enrollment = StudentEnrollment::withoutGlobalScopes()->where('is_current', true)->firstOrFail();

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/reports/student-summary?academic_year_id='.$enrollment->academic_year_id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['records_count', 'present_count', 'absent_count', 'late_count'],
                    'by_student',
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/reports/staff-summary?academic_year_id='.$enrollment->academic_year_id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['records_count', 'present_count', 'absent_count', 'leave_count'],
                    'by_staff',
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/reports/class-attendance?academic_year_id='.$enrollment->academic_year_id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'rows',
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/attendance/reports/defaulters?academic_year_id='.$enrollment->academic_year_id.'&threshold=101')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'threshold',
                    'rows',
                ],
            ]);
    }
}
