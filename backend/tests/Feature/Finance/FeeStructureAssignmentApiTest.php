<?php

namespace Tests\Feature\Finance;

use App\Models\Finance\FeeCategory;
use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeStructure;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeStructureAssignmentApiTest extends TestCase
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

    public function test_authorized_user_can_manage_fee_structures(): void
    {
        $headers = $this->authenticate();
        $category = FeeCategory::withoutGlobalScopes()->where('code', 'TUITION')->firstOrFail();
        $feeHead = FeeHead::withoutGlobalScopes()->where('fee_category_id', $category->id)->where('code', 'MONTHLY-TUITION')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/finance/fee-structures', [
            'academic_year_id' => \App\Models\AcademicYear::withoutGlobalScopes()->where('is_current', true)->value('id'),
            'school_class_id' => \App\Models\SchoolClass::withoutGlobalScopes()->orderBy('id')->value('id'),
            'name' => 'Test Structure',
            'code' => 'TEST-STRUCTURE',
            'description' => 'Testing fee structure',
            'effective_from' => now()->toDateString(),
            'status' => 'active',
            'items' => [
                [
                    'fee_head_id' => $feeHead->id,
                    'amount' => 3000,
                    'due_frequency' => 'monthly',
                    'due_day' => 7,
                    'sort_order' => 1,
                ],
            ],
        ])->assertCreated()->assertJsonPath('data.code', 'TEST-STRUCTURE');

        $this->assertDatabaseHas('finance_fee_structures', ['code' => 'TEST-STRUCTURE']);
    }

    public function test_authorized_user_can_assign_fee_structure_from_current_enrollment(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $feeStructure = FeeStructure::withoutGlobalScopes()->where('code', 'GRADE-FOUNDATION')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/finance/students/{$student->id}/assign-fee-structure", [
            'fee_structure_id' => $feeStructure->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'inactive',
            'remarks' => 'Additional historical assignment for testing.',
        ])->assertCreated()->assertJsonPath('data.student.id', $student->id);
    }
}
