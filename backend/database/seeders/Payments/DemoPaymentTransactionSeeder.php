<?php

namespace Database\Seeders\Payments;

use App\Models\Finance\FeeInvoice;
use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use App\Models\Saas\TenantBillingRecord;
use App\Models\Saas\TenantSubscription;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();
        $invoice = $student
            ? FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->where('student_id', $student->id)->first()
            : null;
        $subscription = TenantSubscription::query()->where('school_id', $school->id)->latest('id')->first();
        $billing = TenantBillingRecord::query()->where('school_id', $school->id)->where('status', 'pending')->latest('id')->first()
            ?: TenantBillingRecord::query()->where('school_id', $school->id)->latest('id')->first();

        $stripe = PaymentGateway::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'stripe-test')->firstOrFail();
        $manualUpi = PaymentGateway::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'manual-upi')->firstOrFail();

        if ($invoice && $student) {
            PaymentTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'transaction_no' => 'TXN-DEMO-STRIPE-001',
                ],
                [
                    'payable_type' => 'school_fee',
                    'payable_id' => $invoice->id,
                    'student_id' => $student->id,
                    'gateway_id' => $stripe->id,
                    'provider' => 'stripe',
                    'payment_method' => 'card',
                    'amount' => min(500, (float) $invoice->balance_amount),
                    'currency' => 'INR',
                    'gateway_order_id' => 'ORDDEMO001',
                    'status' => 'initiated',
                    'verification_status' => 'pending',
                    'metadata' => ['seeded' => true],
                ]
            );

            $upiTransaction = PaymentTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'transaction_no' => 'TXN-DEMO-UPI-001',
                ],
                [
                    'payable_type' => 'school_fee',
                    'payable_id' => $invoice->id,
                    'student_id' => $student->id,
                    'gateway_id' => $manualUpi->id,
                    'provider' => 'upi_manual',
                    'payment_method' => 'upi',
                    'amount' => min(750, (float) $invoice->balance_amount),
                    'currency' => 'INR',
                    'upi_vpa' => 'payer.demo@oksbi',
                    'status' => 'initiated',
                    'verification_status' => 'pending',
                    'metadata' => ['seeded' => true],
                ]
            );

            UpiPaymentRequest::withoutGlobalScopes()->updateOrCreate(
                ['transaction_id' => $upiTransaction->id],
                [
                    'school_id' => $school->id,
                    'upi_vpa' => 'payer.demo@oksbi',
                    'payee_name' => 'Greenwood School',
                    'amount' => $upiTransaction->amount,
                    'currency' => 'INR',
                    'qr_payload' => 'upi://pay?pa=fees.greenwood@oksbi&pn=Greenwood%20School&am='.$upiTransaction->amount.'&cu=INR&tr='.$upiTransaction->transaction_no,
                    'expires_at' => now()->addMinutes(20),
                    'status' => 'pending',
                ]
            );
        }

        if ($subscription && $billing) {
            PaymentTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'transaction_no' => 'TXN-DEMO-SAAS-001',
                ],
                [
                    'payable_type' => 'saas_subscription',
                    'payable_id' => $billing->id,
                    'tenant_subscription_id' => $subscription->id,
                    'gateway_id' => $stripe->id,
                    'provider' => 'stripe',
                    'payment_method' => 'card',
                    'amount' => $billing->amount,
                    'currency' => $billing->currency,
                    'gateway_order_id' => 'ORDSAAS001',
                    'status' => 'initiated',
                    'verification_status' => 'pending',
                    'metadata' => ['seeded' => true, 'token' => Str::uuid()->toString()],
                ]
            );
        }
    }
}
