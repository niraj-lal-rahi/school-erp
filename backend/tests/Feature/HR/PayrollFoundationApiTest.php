<?php

namespace Tests\Feature\HR;

use App\Models\HR\PayrollRun;
use App\Models\HR\SalaryComponent;
use App\Models\HR\Staff;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollFoundationApiTest extends TestCase
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

    public function test_authorized_user_can_manage_salary_components_and_structures(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();

        $componentResponse = $this->withHeaders($headers)->postJson('/api/v1/hr/salary-components', [
            'name' => 'Transport Allowance',
            'code' => 'TA',
            'component_type' => 'earning',
            'calculation_type' => 'fixed',
            'default_value' => 5000,
            'taxable' => true,
            'status' => 'active',
        ])->assertCreated();

        $componentId = $componentResponse->json('data.id');

        $this->withHeaders($headers)->postJson('/api/v1/hr/salary-structures', [
            'staff_id' => $staff->id,
            'effective_from' => '2026-04-01',
            'basic_salary' => 40000,
            'status' => 'active',
            'items' => [
                [
                    'salary_component_id' => $componentId,
                    'amount' => 5000,
                    'percentage' => null,
                    'component_type' => 'earning',
                ],
            ],
        ])->assertCreated()->assertJsonPath('data.staff_id', $staff->id);
    }

    public function test_payroll_run_can_be_processed_finalized_and_paid(): void
    {
        $headers = $this->authenticate();
        $run = PayrollRun::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/hr/payroll-runs/'.$run->id.'/process')
            ->assertOk()
            ->assertJsonPath('data.status', 'processing');

        $this->withHeaders($headers)->postJson('/api/v1/hr/payroll-runs/'.$run->id.'/finalize')
            ->assertOk()
            ->assertJsonPath('data.status', 'finalized');

        $this->withHeaders($headers)->postJson('/api/v1/hr/payroll-runs/'.$run->id.'/mark-paid')
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('staff_payslips', [
            'payroll_run_id' => $run->id,
            'payment_status' => 'paid',
        ]);
    }
}
