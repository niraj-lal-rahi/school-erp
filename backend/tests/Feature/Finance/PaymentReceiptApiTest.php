<?php

namespace Tests\Feature\Finance;

use App\Enums\Finance\PaymentStatus;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReceiptApiTest extends TestCase
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

    public function test_offline_payment_collection_updates_invoice_and_generates_receipt(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('student_id', $student->id)->firstOrFail();
        $installment = FeeInstallment::withoutGlobalScopes()
            ->whereHas('studentFeeAssignment', fn ($query) => $query->where('student_id', $student->id))
            ->where('balance_amount', '>', 0)
            ->firstOrFail();

        $amount = min(500, (float) $invoice->balance_amount, (float) $installment->balance_amount);

        $response = $this->withHeaders($headers)->postJson('/api/v1/finance/payments/collect', [
            'student_id' => $student->id,
            'fee_invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'amount' => $amount,
            'allocations' => [[
                'fee_invoice_id' => $invoice->id,
                'fee_installment_id' => $installment->id,
                'allocated_amount' => $amount,
            ]],
        ])->assertCreated();

        $paymentId = $response->json('data.id');

        $this->assertDatabaseHas('finance_payments', [
            'id' => $paymentId,
            'status' => 'successful',
        ]);

        $this->assertDatabaseHas('finance_receipts', [
            'payment_id' => $paymentId,
        ]);
    }

    public function test_pending_gateway_payment_can_be_confirmed(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->orderBy('id')->firstOrFail();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('student_id', $student->id)->firstOrFail();
        $installment = FeeInstallment::withoutGlobalScopes()
            ->whereHas('studentFeeAssignment', fn ($query) => $query->where('student_id', $student->id))
            ->where('balance_amount', '>', 0)
            ->firstOrFail();

        $amount = min(300, (float) $invoice->balance_amount, (float) $installment->balance_amount);

        $paymentResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/payments/collect', [
            'student_id' => $student->id,
            'fee_invoice_id' => $invoice->id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'online_gateway',
            'gateway_provider' => 'stripe',
            'amount' => $amount,
            'allocations' => [[
                'fee_invoice_id' => $invoice->id,
                'fee_installment_id' => $installment->id,
                'allocated_amount' => $amount,
            ]],
        ])->assertCreated()->assertJsonPath('data.status', 'pending');

        $paymentId = $paymentResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/finance/payments/{$paymentId}/confirm", [
            'gateway_transaction_id' => 'txn_123',
        ])->assertOk()->assertJsonPath('data.status', 'successful');

        $this->assertDatabaseHas('finance_receipts', [
            'payment_id' => $paymentId,
        ]);
    }

    public function test_pending_gateway_payment_can_be_failed(): void
    {
        $headers = $this->authenticate();
        $payment = Payment::withoutGlobalScopes()->create([
            'school_id' => 1,
            'payment_no' => 'PAY-99999',
            'student_id' => 1,
            'fee_invoice_id' => FeeInvoice::withoutGlobalScopes()->value('id'),
            'payment_date' => now()->toDateString(),
            'payment_method' => 'online_gateway',
            'gateway_provider' => 'stripe',
            'amount' => 100,
            'status' => PaymentStatus::Pending->value,
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/finance/payments/{$payment->id}/fail", [
            'remarks' => 'Gateway failure',
        ])->assertOk()->assertJsonPath('data.status', 'failed');
    }
}
