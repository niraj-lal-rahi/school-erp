<?php

namespace Tests\Feature\HR;

use App\Models\HR\Staff;
use App\Support\Auth\JwtManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffLifecycleAndProfileApiTest extends TestCase
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

    public function test_authorized_user_can_manage_bank_details_notes_and_status_history(): void
    {
        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0001')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/bank-details', [
            'bank_name' => 'HDFC Bank',
            'account_holder_name' => 'School Admin',
            'account_number' => '999999999999',
            'ifsc_code' => 'HDFC0009876',
            'branch_name' => 'MG Road',
            'account_type' => 'Savings',
            'is_primary' => true,
        ])->assertCreated()->assertJsonPath('data.staff_id', $staff->id);

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/notes', [
            'note' => 'Needs leadership training follow-up.',
            'visibility_type' => 'internal',
        ])->assertCreated()->assertJsonPath('data.staff_id', $staff->id);

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/suspend', [
            'action_type' => 'suspend',
            'new_status' => 'suspended',
            'reason' => 'Policy review pending',
            'effective_date' => '2026-04-24',
        ])->assertOk()->assertJsonPath('data.current_status', 'suspended');

        $this->withHeaders($headers)->getJson('/api/v1/hr/staff/'.$staff->id.'/status-history')
            ->assertOk()
            ->assertJsonFragment([
                'new_status' => 'suspended',
                'action_type' => 'suspend',
            ]);
    }
}
