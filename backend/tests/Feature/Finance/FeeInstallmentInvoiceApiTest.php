<?php

namespace Tests\Feature\Finance;

use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeInstallmentInvoiceApiTest extends TestCase
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

    public function test_authorized_user_can_generate_installments_for_assignment(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $enrollment = $student->enrollments()->where('is_current', true)->firstOrFail();
        $feeStructure = FeeStructure::withoutGlobalScopes()->where('code', 'GRADE-FOUNDATION')->firstOrFail();

        $assignment = StudentFeeAssignment::withoutGlobalScopes()->create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'academic_year_id' => $enrollment->academic_year_id,
            'school_class_id' => $enrollment->school_class_id,
            'section_id' => $enrollment->section_id,
            'fee_structure_id' => $feeStructure->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'inactive',
            'remarks' => 'Generated in test.',
        ]);

        $this->withHeaders($headers)
            ->postJson("/api/v1/finance/student-fee-assignments/{$assignment->id}/generate-installments")
            ->assertCreated()
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('finance_fee_installments', [
            'student_fee_assignment_id' => $assignment->id,
            'status' => 'pending',
        ]);
    }

    public function test_authorized_user_can_create_and_issue_invoice(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $enrollment = $student->enrollments()->where('is_current', true)->firstOrFail();
        $feeStructure = FeeStructure::withoutGlobalScopes()->where('code', 'GRADE-FOUNDATION')->firstOrFail();
        $assignment = StudentFeeAssignment::withoutGlobalScopes()->create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'academic_year_id' => $enrollment->academic_year_id,
            'school_class_id' => $enrollment->school_class_id,
            'section_id' => $enrollment->section_id,
            'fee_structure_id' => $feeStructure->id,
            'assigned_date' => now()->toDateString(),
            'status' => 'inactive',
            'remarks' => 'Invoice test assignment.',
        ]);

        $this->withHeaders($headers)
            ->postJson("/api/v1/finance/student-fee-assignments/{$assignment->id}/generate-installments")
            ->assertCreated();

        $installmentIds = FeeInstallment::withoutGlobalScopes()
            ->where('student_fee_assignment_id', $assignment->id)
            ->orderBy('due_date')
            ->limit(2)
            ->pluck('id')
            ->all();

        $invoiceResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/finance/invoices', [
                'student_id' => $student->id,
                'academic_year_id' => $assignment->academic_year_id,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'notes' => 'Invoice from test.',
                'installment_ids' => $installmentIds,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft');

        $invoiceId = $invoiceResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/finance/invoices/{$invoiceId}/issue")
            ->assertOk()
            ->assertJsonPath('data.status', 'issued');
    }
}
