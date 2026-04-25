<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance\AttendanceStatusType;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use App\Models\HR\StaffLeaveApplication;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAttendanceApiTest extends TestCase
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

    public function test_authorized_user_can_record_staff_attendance_from_attendance_module(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();
        $presentStatus = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $staff->school_id)
            ->where('code', 'PRESENT')
            ->firstOrFail();

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/staff-records', [
                'staff_id' => $staff->id,
                'attendance_date' => '2026-04-24',
                'check_in_time' => '08:05',
                'check_out_time' => '16:10',
                'attendance_status_type_id' => $presentStatus->id,
                'source' => 'manual',
                'remarks' => 'Marked from attendance module.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.staff_id', $staff->id)
            ->assertJsonPath('data.attendance_status', 'present')
            ->assertJsonPath('data.attendance_status_type.code', 'PRESENT');
    }

    public function test_approved_leave_forces_leave_status_on_staff_attendance(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();
        $leaveType = LeaveType::withoutGlobalScopes()->where('school_id', $staff->school_id)->firstOrFail();

        StaffLeaveApplication::withoutGlobalScopes()->create([
            'school_id' => $staff->school_id,
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-25',
            'end_date' => '2026-04-25',
            'total_days' => 1,
            'reason' => 'Medical leave',
            'status' => 'approved',
            'reviewed_by' => User::withoutGlobalScopes()->where('school_id', $staff->school_id)->value('id'),
            'reviewed_at' => now(),
            'review_remarks' => 'Approved in test.',
        ]);

        $presentStatus = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $staff->school_id)
            ->where('code', 'PRESENT')
            ->firstOrFail();

        $this->withHeaders($headers)
            ->postJson('/api/v1/attendance/staff/'.$staff->id.'/mark', [
                'attendance_date' => '2026-04-25',
                'attendance_status_type_id' => $presentStatus->id,
                'source' => 'manual',
                'remarks' => 'Should be overridden to leave.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.attendance_status', 'leave')
            ->assertJsonPath('data.attendance_status_type.code', 'LEAVE');
    }
}
