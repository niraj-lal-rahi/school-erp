<?php

namespace Tests\Feature\HR;

use App\Models\HR\Staff;
use App\Support\Auth\JwtManager;
use App\Models\User;
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

    public function test_authorized_user_can_record_and_fetch_staff_attendance(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/attendance', [
            'attendance_date' => '2026-04-23',
            'check_in_time' => '08:10',
            'check_out_time' => '16:00',
            'attendance_status' => 'present',
            'source' => 'manual',
            'remarks' => 'On-time attendance',
        ])->assertCreated()->assertJsonPath('data.staff_id', $staff->id);

        $this->withHeaders($headers)->getJson('/api/v1/hr/staff/'.$staff->id.'/attendance?date_from=2026-04-01&date_to=2026-04-30')
            ->assertOk()
            ->assertJsonFragment([
                'attendance_status' => 'present',
                'attendance_date' => '2026-04-23',
            ]);
    }

    public function test_duplicate_attendance_for_same_staff_and_date_is_rejected(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff-attendance', [
            'staff_id' => $staff->id,
            'attendance_date' => now()->toDateString(),
            'attendance_status' => 'present',
            'source' => 'manual',
        ])->assertStatus(422);
    }
}
