<?php

namespace Tests\Feature\HR;

use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\Staff;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementApiTest extends TestCase
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

    public function test_authorized_user_can_manage_leave_types_and_balances(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();

        $typeResponse = $this->withHeaders($headers)->postJson('/api/v1/hr/leave-types', [
            'name' => 'Sick Leave',
            'code' => 'SL',
            'annual_quota' => 10,
            'carry_forward_allowed' => true,
            'paid_leave' => true,
            'status' => 'active',
        ])->assertCreated();

        $leaveTypeId = $typeResponse->json('data.id');

        $this->withHeaders($headers)->getJson('/api/v1/hr/leave-balances?staff_id='.$staff->id.'&leave_type_id='.$leaveTypeId)
            ->assertOk();
    }

    public function test_leave_application_workflow_updates_balance_and_attendance(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();
        $leaveType = LeaveType::withoutGlobalScopes()->where('code', 'CL')->firstOrFail();

        $createResponse = $this->withHeaders($headers)->postJson('/api/v1/hr/leave-applications', [
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-25',
            'end_date' => '2026-04-26',
            'reason' => 'Medical rest',
            'status' => 'draft',
        ])->assertCreated();

        $leaveApplicationId = $createResponse->json('data.id');

        $this->withHeaders($headers)->postJson('/api/v1/hr/leave-applications/'.$leaveApplicationId.'/submit')
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');

        $this->withHeaders($headers)->postJson('/api/v1/hr/leave-applications/'.$leaveApplicationId.'/approve', [
            'review_remarks' => 'Approved by HR',
        ])->assertOk()->assertJsonPath('data.status', 'approved');

        $balance = LeaveBalance::withoutGlobalScopes()
            ->where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->firstOrFail();

        $this->assertSame('2.00', $balance->used_days);
        $this->assertDatabaseHas('staff_attendance', [
            'staff_id' => $staff->id,
            'attendance_status' => 'leave',
        ]);

        $this->withHeaders($headers)->postJson('/api/v1/hr/leave-applications/'.$leaveApplicationId.'/cancel', [
            'review_remarks' => 'Cancelled after plan change',
        ])->assertOk()->assertJsonPath('data.status', 'cancelled');
    }
}
