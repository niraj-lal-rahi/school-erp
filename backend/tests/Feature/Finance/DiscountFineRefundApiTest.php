<?php

namespace Tests\Feature\Finance;

use App\Models\Finance\DiscountType;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FineRule;
use App\Models\Finance\Payment;
use App\Models\Finance\Refund;
use App\Models\Finance\StudentDiscount;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountFineRefundApiTest extends TestCase
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

    public function test_authorized_user_can_manage_discount_types_and_fine_rules(): void
    {
        $headers = $this->authenticate();

        $discountResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/discount-types', [
            'name' => 'Sibling Benefit',
            'code' => 'SIBLING',
            'discount_type' => 'fixed',
            'value' => 750,
            'max_amount' => 750,
            'description' => 'Sibling concession.',
            'status' => 'active',
        ])->assertCreated();

        $discountId = $discountResponse->json('data.id');

        $this->withHeaders($headers)->putJson("/api/v1/finance/discount-types/{$discountId}", [
            'name' => 'Sibling Benefit Updated',
            'code' => 'SIBLING',
            'discount_type' => 'fixed',
            'value' => 800,
            'max_amount' => 800,
            'description' => 'Updated sibling concession.',
            'status' => 'active',
        ])->assertOk()->assertJsonPath('data.value', '800.00');

        $fineResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/fine-rules', [
            'name' => 'Exam Late Fine',
            'code' => 'EXAM-LATE',
            'fine_type' => 'fixed',
            'amount' => 150,
            'grace_days' => 2,
            'status' => 'active',
        ])->assertCreated();

        $this->assertDatabaseHas('finance_discount_types', ['id' => $discountId, 'code' => 'SIBLING']);
        $this->assertDatabaseHas('finance_fine_rules', ['id' => $fineResponse->json('data.id'), 'code' => 'EXAM-LATE']);
    }

    public function test_approved_discount_and_fine_can_be_applied_to_invoice(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('student_id', $student->id)->firstOrFail();
        $studentDiscount = StudentDiscount::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->firstOrFail();
        $fineRule = FineRule::withoutGlobalScopes()->where('status', 'active')->firstOrFail();

        $originalGrandTotal = (float) $invoice->grand_total;
        $originalFineTotal = (float) $invoice->fine_total;

        $this->withHeaders($headers)->postJson("/api/v1/finance/invoices/{$invoice->id}/apply-discount", [
            'student_discount_id' => $studentDiscount->id,
        ])->assertOk();

        $invoice->refresh();
        $this->assertLessThan($originalGrandTotal, (float) $invoice->grand_total);

        $this->withHeaders($headers)->postJson("/api/v1/finance/invoices/{$invoice->id}/apply-fine", [
            'fine_rule_id' => $fineRule->id,
        ])->assertOk();

        $invoice->refresh();
        $this->assertGreaterThanOrEqual($originalFineTotal, (float) $invoice->fine_total);
    }

    public function test_refund_can_be_requested_approved_and_processed(): void
    {
        $headers = $this->authenticate();
        $payment = Payment::withoutGlobalScopes()->where('status', 'successful')->firstOrFail();
        $student = Student::withoutGlobalScopes()->findOrFail($payment->student_id);
        $invoice = FeeInvoice::withoutGlobalScopes()->findOrFail($payment->fee_invoice_id);
        $originalPaidAmount = (float) $invoice->paid_amount;

        $refundResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/refunds', [
            'payment_id' => $payment->id,
            'student_id' => $student->id,
            'refund_date' => now()->toDateString(),
            'amount' => min(100, (float) $payment->amount),
            'reason' => 'Test refund request.',
        ])->assertCreated();

        $refundId = $refundResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/finance/refunds/{$refundId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->withHeaders($headers)->postJson("/api/v1/finance/refunds/{$refundId}/process")
            ->assertOk()
            ->assertJsonPath('data.status', 'processed');

        $invoice->refresh();
        $payment->refresh();

        $this->assertLessThan($originalPaidAmount, (float) $invoice->paid_amount);
        $this->assertDatabaseHas('finance_refunds', [
            'id' => $refundId,
            'status' => 'processed',
        ]);
        $this->assertContains($payment->status, ['successful', 'refunded']);
    }
}
