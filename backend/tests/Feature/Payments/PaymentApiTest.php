<?php

namespace Tests\Feature\Payments;

use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment as FinancePayment;
use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\PaymentWebhookEvent;
use App\Models\Role;
use App\Models\Saas\Tenant;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantSubscription;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\Payments\PaymentWebhookService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tenantAdminHeaders(string $email = 'admin@greenwood.edu', string $tenantCode = 'greenwood'): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => $tenantCode,
        ];
    }

    protected function firstStudentInvoice(): array
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->firstOrFail();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->where('student_id', $student->id)->firstOrFail();

        return [$school, $student, $invoice];
    }

    protected function gateway(string $code): PaymentGateway
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        return PaymentGateway::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', $code)
            ->firstOrFail();
    }

    protected function initiateUpiPayment(array $headers, array $extra = []): array
    {
        [, $student, $invoice] = $this->firstStudentInvoice();
        $gateway = $this->gateway('manual-upi');

        $response = $this->withHeaders($headers)->postJson('/api/v1/payments/upi/initiate', array_merge([
            'payable_type' => 'school_fee',
            'payable_id' => $invoice->id,
            'student_id' => $student->id,
            'gateway_id' => $gateway->id,
            'provider' => 'upi_manual',
            'payment_method' => 'upi',
            'amount' => min(300, (float) $invoice->balance_amount),
            'currency' => 'INR',
            'upi_vpa' => 'payer.demo@oksbi',
        ], $extra));

        return [$response, $student, $invoice, $gateway];
    }

    protected function initiateSchoolFeePayment(array $headers, string $gatewayCode, string $provider, string $method, array $extra = []): array
    {
        [, $student, $invoice] = $this->firstStudentInvoice();
        $gateway = $this->gateway($gatewayCode);

        $response = $this->withHeaders($headers)->postJson('/api/v1/payments/initiate', array_merge([
            'payable_type' => 'school_fee',
            'payable_id' => $invoice->id,
            'student_id' => $student->id,
            'gateway_id' => $gateway->id,
            'provider' => $provider,
            'payment_method' => $method,
            'amount' => min(300, (float) $invoice->balance_amount),
            'currency' => 'INR',
        ], $extra));

        return [$response, $student, $invoice, $gateway];
    }

    public function test_can_initiate_razorpay_payment(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$response] = $this->initiateSchoolFeePayment($headers, 'razorpay-test', 'razorpay', 'upi');

        $response->assertCreated()
            ->assertJsonPath('data.transaction.provider', 'razorpay')
            ->assertJsonPath('data.transaction.status', 'initiated')
            ->assertJsonPath('data.gateway.code', 'razorpay-test');
    }

    public function test_can_verify_razorpay_signature(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation] = $this->initiateSchoolFeePayment($headers, 'razorpay-test', 'razorpay', 'upi');
        $transactionId = $initiation->json('data.transaction.id');
        $orderId = $initiation->json('data.payload.gateway_order_id');
        $paymentId = 'pay_test_razorpay_001';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, 'rzp_test_secret');

        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', [
            'transaction_id' => $transactionId,
            'provider' => 'razorpay',
            'gateway_order_id' => $orderId,
            'gateway_payment_id' => $paymentId,
            'gateway_signature' => $signature,
        ])->assertOk()
            ->assertJsonPath('data.status', 'successful')
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'status' => 'successful',
            'verification_status' => 'verified',
        ]);
    }

    public function test_can_initiate_manual_upi_payment(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$response] = $this->initiateUpiPayment($headers, [
            'payee_name' => 'Greenwood School',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.transaction.provider', 'upi_manual')
            ->assertJsonPath('data.upi_request.status', 'pending');

        $this->assertStringContainsString(
            'upi://pay?',
            (string) $response->json('data.upi_request.qr_payload')
        );
    }

    public function test_invalid_upi_vpa_is_rejected(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$response] = $this->initiateUpiPayment($headers, [
            'upi_vpa' => 'not-a-valid-vpa',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['upi_vpa']);
    }

    public function test_can_manually_verify_upi_payment(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation] = $this->initiateUpiPayment($headers);

        $transactionId = $initiation->json('data.transaction.id');

        $this->withHeaders($headers)->postJson("/api/v1/payments/upi/{$transactionId}/manual-verify", [
            'upi_reference_no' => 'UPI-REF-12345',
            'remarks' => 'Verified by accountant desk.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'manually_verified')
            ->assertJsonPath('data.verification_status', 'manual_review');

        $this->assertDatabaseHas('upi_payment_requests', [
            'transaction_id' => $transactionId,
            'status' => 'verified',
        ]);
    }

    public function test_duplicate_payment_verification_does_not_double_credit_invoice(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation, $student, $invoice] = $this->initiateSchoolFeePayment($headers, 'razorpay-test', 'razorpay', 'upi');
        $transactionId = $initiation->json('data.transaction.id');
        $transactionNo = $initiation->json('data.transaction.transaction_no');
        $orderId = $initiation->json('data.payload.gateway_order_id');
        $paymentId = 'pay_test_razorpay_002';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, 'rzp_test_secret');

        $payload = [
            'transaction_id' => $transactionId,
            'provider' => 'razorpay',
            'gateway_order_id' => $orderId,
            'gateway_payment_id' => $paymentId,
            'gateway_signature' => $signature,
        ];

        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', $payload)->assertOk();
        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', $payload)->assertOk();

        $this->assertSame(
            1,
            FinancePayment::withoutGlobalScopes()
                ->where('school_id', $student->school_id)
                ->where('fee_invoice_id', $invoice->id)
                ->where('gateway_transaction_id', $paymentId)
                ->count()
        );

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transactionId,
            'transaction_no' => $transactionNo,
            'status' => 'successful',
        ]);
    }

    public function test_webhook_processing_is_idempotent(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation] = $this->initiateSchoolFeePayment($headers, 'razorpay-test', 'razorpay', 'upi');
        $orderId = $initiation->json('data.payload.gateway_order_id');
        $paymentId = 'pay_webhook_payment_001';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, 'rzp_test_secret');

        $payload = [
            'id' => 'evt_test_razorpay_001',
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                    ],
                ],
            ],
        ];

        $this->withHeaders([
            'X-Razorpay-Signature' => $signature,
        ])->postJson('/api/v1/payments/webhooks/razorpay', $payload)->assertAccepted();

        $this->withHeaders([
            'X-Razorpay-Signature' => $signature,
        ])->postJson('/api/v1/payments/webhooks/razorpay', $payload)->assertAccepted();

        $this->assertDatabaseCount('payment_webhook_events', 1);

        $event = PaymentWebhookEvent::withoutGlobalScopes()
            ->where('provider', 'razorpay')
            ->latest('id')
            ->firstOrFail();
        app(PaymentWebhookService::class)->processWebhook($event);

        $transaction = PaymentTransaction::withoutGlobalScopes()
            ->where('gateway_order_id', $orderId)
            ->firstOrFail();

        $this->assertSame('successful', $transaction->status);
        $this->assertSame($paymentId, $transaction->gateway_payment_id);
    }

    public function test_can_request_refund_for_successful_payment(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation] = $this->initiateSchoolFeePayment($headers, 'stripe-test', 'stripe', 'card');
        $transactionId = $initiation->json('data.transaction.id');

        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', [
            'transaction_id' => $transactionId,
            'provider' => 'stripe',
            'gateway_payment_id' => 'pi_refund_test_001',
        ])->assertOk();

        $this->withHeaders($headers)->postJson("/api/v1/payments/transactions/{$transactionId}/refund", [
            'amount' => 100,
            'reason' => 'Duplicate collection reversal',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'requested');

        $this->assertDatabaseHas('payment_refunds', [
            'transaction_id' => $transactionId,
            'status' => 'requested',
        ]);
    }

    public function test_successful_school_fee_payment_syncs_finance_records(): void
    {
        $headers = $this->tenantAdminHeaders();

        [$initiation, $student, $invoice] = $this->initiateSchoolFeePayment($headers, 'stripe-test', 'stripe', 'card');
        $transactionId = $initiation->json('data.transaction.id');
        $paymentIntentId = 'pi_finance_sync_001';

        $beforeCount = FinancePayment::withoutGlobalScopes()
            ->where('school_id', $student->school_id)
            ->where('fee_invoice_id', $invoice->id)
            ->count();

        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', [
            'transaction_id' => $transactionId,
            'provider' => 'stripe',
            'gateway_payment_id' => $paymentIntentId,
        ])->assertOk();

        $this->assertSame(
            $beforeCount + 1,
            FinancePayment::withoutGlobalScopes()
                ->where('school_id', $student->school_id)
                ->where('fee_invoice_id', $invoice->id)
                ->count()
        );

        $this->assertDatabaseHas('finance_payments', [
            'school_id' => $student->school_id,
            'fee_invoice_id' => $invoice->id,
            'gateway_transaction_id' => $paymentIntentId,
        ]);

        $payment = FinancePayment::withoutGlobalScopes()
            ->where('school_id', $student->school_id)
            ->where('gateway_transaction_id', $paymentIntentId)
            ->firstOrFail();

        $this->assertDatabaseHas('finance_receipts', [
            'payment_id' => $payment->id,
        ]);
    }

    public function test_successful_saas_subscription_payment_syncs_billing(): void
    {
        $headers = $this->tenantAdminHeaders();
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $subscription = TenantSubscription::query()->where('school_id', $school->id)->latest('id')->firstOrFail();
        $billing = TenantBillingRecord::query()->create([
            'school_id' => $school->id,
            'subscription_id' => $subscription->id,
            'invoice_no' => 'INV-PAYMENT-TEST-001',
            'amount' => 2499,
            'currency' => 'INR',
            'billing_cycle' => 'monthly',
            'billing_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);
        $gateway = $this->gateway('stripe-test');

        $response = $this->withHeaders($headers)->postJson('/api/v1/payments/initiate', [
            'payable_type' => 'saas_subscription',
            'payable_id' => $billing->id,
            'tenant_subscription_id' => $subscription->id,
            'gateway_id' => $gateway->id,
            'provider' => 'stripe',
            'payment_method' => 'card',
            'amount' => (float) $billing->amount,
            'currency' => $billing->currency,
        ])->assertCreated();

        $transactionId = $response->json('data.transaction.id');

        $this->withHeaders($headers)->postJson('/api/v1/payments/verify', [
            'transaction_id' => $transactionId,
            'provider' => 'stripe',
            'gateway_payment_id' => 'pi_saas_sync_001',
        ])->assertOk();

        $this->assertDatabaseHas('tenant_billing_records', [
            'id' => $billing->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'id' => $subscription->id,
            'status' => 'active',
        ]);
    }

    public function test_tenant_isolation_blocks_other_tenant_from_accessing_transaction(): void
    {
        $headers = $this->tenantAdminHeaders();
        [$initiation] = $this->initiateSchoolFeePayment($headers, 'stripe-test', 'stripe', 'card');
        $transactionId = $initiation->json('data.transaction.id');

        $riverside = Tenant::withoutGlobalScopes()->where('code', 'riverside')->firstOrFail();
        $tenantRole = Role::withoutGlobalScopes()->firstOrCreate(
            [
                'school_id' => $riverside->id,
                'slug' => 'school-admin',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Tenant Administrator',
                'code' => 'tenant_admin',
                'scope' => 'tenant',
                'description' => 'Riverside tenant administrator.',
                'role_type' => 'tenant',
                'is_default' => true,
                'status' => 'active',
            ]
        );

        $riversideUser = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $riverside->id,
                'email' => 'admin@riverside.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riverside',
                'last_name' => 'Admin',
                'name' => 'Riverside Admin',
                'phone' => '9555555555',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $riversideUser->roles()->syncWithoutDetaching([
            $tenantRole->id => ['school_id' => $riverside->id],
        ]);

        $token = app(JwtManager::class)->issueAccessToken($riversideUser);
        $riversideHeaders = [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'riverside',
        ];

        $this->withHeaders($riversideHeaders)
            ->getJson("/api/v1/payments/transactions/{$transactionId}")
            ->assertForbidden();
    }
}
