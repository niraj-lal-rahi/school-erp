<?php

namespace Tests\Feature\HR;

use App\Models\HR\Staff;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffHrApiTest extends TestCase
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

    public function test_authorized_user_can_create_department_designation_and_staff(): void
    {
        $headers = $this->authenticate();

        $departmentResponse = $this->withHeaders($headers)->postJson('/api/v1/hr/departments', [
            'name' => 'Accounts',
            'code' => 'ACC',
            'description' => 'Accounts and finance team',
            'status' => 'active',
        ])->assertCreated();

        $departmentId = $departmentResponse->json('data.id');

        $designationResponse = $this->withHeaders($headers)->postJson('/api/v1/hr/designations', [
            'department_id' => $departmentId,
            'name' => 'Accountant',
            'code' => 'ACCOUNTANT',
            'description' => 'Manages fee reconciliation and accounting records',
            'status' => 'active',
        ])->assertCreated();

        $designationId = $designationResponse->json('data.id');

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff', [
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'employee_code' => 'EMP-0100',
            'first_name' => 'Rohit',
            'last_name' => 'Kapoor',
            'gender' => 'male',
            'email' => 'rohit.kapoor@greenwood.edu',
            'phone' => '9888888888',
            'staff_type' => 'non_teaching',
            'employment_type' => 'full_time',
            'joining_date' => '2026-04-20',
            'current_status' => 'active',
        ])->assertCreated()->assertJsonPath('data.full_name', 'Rohit Kapoor');

        $this->assertDatabaseHas('hr_departments', ['code' => 'ACC']);
        $this->assertDatabaseHas('hr_designations', ['code' => 'ACCOUNTANT']);
        $this->assertDatabaseHas('staff', ['employee_code' => 'EMP-0100', 'full_name' => 'Rohit Kapoor']);
    }

    public function test_staff_listing_supports_search_and_filters(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();

        $this->withHeaders($headers)->getJson('/api/v1/hr/staff?search=Meera&staff_type=teaching')
            ->assertOk()
            ->assertJsonPath('data.0.id', $staff->id);
    }

    public function test_academic_options_uses_hr_staff_records(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();

        $this->withHeaders($headers)->getJson('/api/v1/academic-management/options')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $staff->id,
                'name' => $staff->full_name,
                'employee_code' => $staff->employee_code,
            ]);
    }
}
