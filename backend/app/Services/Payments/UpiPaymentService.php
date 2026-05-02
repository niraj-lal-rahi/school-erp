<?php

namespace App\Services\Payments;

use App\Contracts\Payments\SupportsUpiInterface;
use App\Events\Payments\UpiPaymentManuallyVerified;
use App\Events\Payments\UpiPaymentPendingVerification;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use App\Models\User;
use App\Repositories\Contracts\Payments\UpiPaymentRequestRepositoryInterface;
use App\Services\Rbac\AccessControlService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpiPaymentService
{
    public function __construct(
        protected UpiPaymentRequestRepositoryInterface $upiRequests,
        protected PaymentTransactionService $transactions,
        protected PaymentGatewayService $gateways,
        protected AccessControlService $accessControl,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->upiRequests->paginate($filters, $perPage);
    }

    public function findByTransactionId(int $transactionId): ?UpiPaymentRequest
    {
        return $this->upiRequests->findByTransactionId($transactionId);
    }

    public function createUpiPaymentRequest(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $result = $this->transactions->initiatePayment(array_merge($attributes, [
                'provider' => $attributes['provider'] ?? 'upi_manual',
                'payment_method' => 'upi',
            ]));

            $transaction = $result['transaction'];
            $gatewayImplementation = $this->gateways->resolveGatewayImplementation($result['gateway']);

            if (! $gatewayImplementation instanceof SupportsUpiInterface) {
                throw ValidationException::withMessages([
                    'gateway' => ['The selected gateway does not support UPI payments.'],
                ]);
            }

            $upiPayload = $gatewayImplementation->createUpiPaymentRequest($transaction, $attributes);
            $upiRequest = $this->upiRequests->findByTransactionId($transaction->id)
                ?? $this->upiRequests->create([
                    'school_id' => $transaction->school_id,
                    'transaction_id' => $transaction->id,
                    'upi_vpa' => $upiPayload['upi_vpa'],
                    'payee_name' => $upiPayload['payee_name'] ?? null,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'qr_payload' => $upiPayload['qr_payload'] ?? null,
                    'expires_at' => $attributes['expires_at'] ?? now()->addMinutes(30),
                    'status' => $upiPayload['status'] ?? 'pending',
                ]);

            $transaction = $this->transactions->findOrFail($transaction->id);

            DB::afterCommit(fn () => event(new UpiPaymentPendingVerification($upiRequest, $transaction)));

            return [
                'transaction' => $transaction,
                'upi_request' => $upiRequest,
                'gateway_payload' => $upiPayload,
            ];
        });
    }

    public function verifyUpiReference(PaymentTransaction $transaction, string $referenceNumber, array $payload = []): PaymentTransaction
    {
        $gateway = $transaction->gateway ?: $this->gateways->getActiveGatewayForTenant($transaction->provider, $transaction->school_id);
        $implementation = $this->gateways->resolveGatewayImplementation($gateway);

        if (! $implementation instanceof SupportsUpiInterface) {
            throw ValidationException::withMessages([
                'gateway' => ['The selected gateway does not support UPI verification.'],
            ]);
        }

        $result = $implementation->verifyUpiReference($transaction, $referenceNumber, $payload);

        return $this->transactions->verifyPayment($transaction, array_merge($payload, [
            'upi_reference_no' => $referenceNumber,
            'status' => $result['status'],
            'verification_status' => $result['verification_status'],
        ]));
    }

    public function manualVerify(UpiPaymentRequest $upiPaymentRequest, array $payload, User $performedBy): PaymentTransaction
    {
        $this->assertManualVerificationPermission($performedBy);

        $transaction = $upiPaymentRequest->transaction;
        $transaction = $this->transactions->manualApprove($transaction, [
            'status' => 'manually_verified',
            'verification_status' => 'manual_review',
            'upi_reference_no' => $payload['upi_reference_no'] ?? $transaction->upi_reference_no,
            'remarks' => $payload['remarks'] ?? 'UPI payment manually verified.',
            'metadata' => $payload['metadata'] ?? [],
        ], $performedBy);

        $this->upiRequests->update($upiPaymentRequest, [
            'status' => 'verified',
        ]);

        DB::afterCommit(fn () => event(new UpiPaymentManuallyVerified($upiPaymentRequest, $transaction, $performedBy)));

        return $transaction;
    }

    public function expireRequest(UpiPaymentRequest $upiPaymentRequest): UpiPaymentRequest
    {
        return DB::transaction(function () use ($upiPaymentRequest): UpiPaymentRequest {
            $request = $this->upiRequests->update($upiPaymentRequest, ['status' => 'expired']);
            $this->transactions->cancel($request->transaction, null, 'UPI request expired.');

            return $request;
        });
    }

    public function expireUnpaidRequests(): int
    {
        return $this->upiRequests->expireDue();
    }

    protected function assertManualVerificationPermission(User $performedBy): void
    {
        if (
            $this->accessControl->isSuperAdmin($performedBy)
            || $this->accessControl->checkRole($performedBy, 'accountant')
            || $this->accessControl->checkRole($performedBy, 'tenant_admin')
            || $this->accessControl->checkPermission($performedBy, 'finance.manage')
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'user' => ['You are not authorized to manually verify UPI payments.'],
        ]);
    }
}
