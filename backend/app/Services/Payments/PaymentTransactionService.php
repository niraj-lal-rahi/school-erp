<?php

namespace App\Services\Payments;

use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentInitiated;
use App\Events\Payments\PaymentSuccessful;
use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentTransaction;
use App\Models\User;
use App\Repositories\Contracts\Payments\PaymentTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentTransactionService
{
    public function __construct(
        protected PaymentTransactionRepositoryInterface $transactions,
        protected PaymentGatewayService $gatewayService,
        protected PaymentReconciliationService $reconciliations,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactions->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): PaymentTransaction
    {
        return $this->transactions->findOrFail($id);
    }

    public function findByGatewayPaymentId(string $gatewayPaymentId, ?int $schoolId = null): ?PaymentTransaction
    {
        return $this->transactions->findByGatewayPaymentId($gatewayPaymentId, $schoolId);
    }

    public function findByGatewayOrderId(string $gatewayOrderId, ?int $schoolId = null): ?PaymentTransaction
    {
        return $this->transactions->findByGatewayOrderId($gatewayOrderId, $schoolId);
    }

    public function listByPayable(string $payableType, ?int $payableId, int $schoolId): Collection
    {
        return $this->transactions->listByPayable($payableType, $payableId, $schoolId);
    }

    public function paymentSummary(array $filters = []): array
    {
        $query = PaymentTransaction::withoutGlobalScopes()
            ->when($filters['school_id'] ?? null, fn (Builder $builder, $value) => $builder->where('school_id', $value))
            ->when($filters['provider'] ?? null, fn (Builder $builder, string $value) => $builder->where('provider', $value))
            ->when($filters['payment_method'] ?? null, fn (Builder $builder, string $value) => $builder->where('payment_method', $value))
            ->when($filters['status'] ?? null, fn (Builder $builder, string $value) => $builder->where('status', $value))
            ->when($filters['verification_status'] ?? null, fn (Builder $builder, string $value) => $builder->where('verification_status', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $builder, $value) => $builder->where('student_id', $value))
            ->when($filters['payable_type'] ?? null, fn (Builder $builder, string $value) => $builder->where('payable_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '<=', $value));

        return [
            'total_transactions' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('amount'),
            'successful_transactions' => (clone $query)->whereIn('status', ['successful', 'manually_verified'])->count(),
            'successful_amount' => (float) (clone $query)->whereIn('status', ['successful', 'manually_verified'])->sum('amount'),
            'failed_transactions' => (clone $query)->where('status', 'failed')->count(),
            'pending_transactions' => (clone $query)->whereIn('status', ['pending', 'initiated'])->count(),
            'refunded_amount' => (float) (clone $query)->where('status', 'refunded')->sum('amount'),
            'by_provider' => (clone $query)
                ->selectRaw('provider, COUNT(*) as transactions_count, COALESCE(SUM(amount), 0) as total_amount')
                ->groupBy('provider')
                ->get(),
            'by_method' => (clone $query)
                ->selectRaw('payment_method, COUNT(*) as transactions_count, COALESCE(SUM(amount), 0) as total_amount')
                ->groupBy('payment_method')
                ->get(),
        ];
    }

    public function createTransaction(array $attributes): PaymentTransaction
    {
        return DB::transaction(function () use ($attributes): PaymentTransaction {
            return $this->transactions->create([
                'school_id' => $attributes['school_id'],
                'transaction_no' => $attributes['transaction_no'] ?? $this->generateTransactionNo((int) $attributes['school_id']),
                'payable_type' => $attributes['payable_type'],
                'payable_id' => $attributes['payable_id'] ?? null,
                'student_id' => $attributes['student_id'] ?? null,
                'tenant_subscription_id' => $attributes['tenant_subscription_id'] ?? null,
                'gateway_id' => $attributes['gateway_id'] ?? null,
                'provider' => $attributes['provider'],
                'payment_method' => $attributes['payment_method'],
                'amount' => $attributes['amount'],
                'currency' => strtoupper($attributes['currency'] ?? 'INR'),
                'upi_vpa' => $attributes['upi_vpa'] ?? null,
                'status' => $attributes['status'] ?? 'pending',
                'verification_status' => $attributes['verification_status'] ?? 'pending',
                'metadata' => $attributes['metadata'] ?? [],
            ]);
        });
    }

    public function initiatePayment(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $transaction = $this->createTransaction(array_merge($attributes, [
                'status' => 'initiated',
            ]));

            $gateway = $this->resolveGatewayForTransaction($transaction, $attributes['gateway_id'] ?? null);
            $implementation = $this->gatewayService->resolveGatewayImplementation($gateway);
            $payload = $implementation->initiatePayment($transaction, $attributes);

            $transaction = $this->transactions->update($transaction, [
                'gateway_id' => $gateway->id,
                'gateway_order_id' => $payload['gateway_order_id'] ?? null,
                'upi_qr_payload' => $payload['upi_request']['qr_payload'] ?? ($attributes['upi_qr_payload'] ?? null),
                'metadata' => array_merge($transaction->metadata ?? [], ['initiation' => $payload]),
            ]);

            $this->reconciliations->reconcile(
                $transaction,
                'gateway_api',
                'initiated',
                null,
                'Payment initiated.',
                ['idempotency_key' => 'initiate:'.$transaction->transaction_no]
            );

            DB::afterCommit(fn () => event(new PaymentInitiated($transaction, $payload)));

            return [
                'transaction' => $transaction,
                'gateway' => $gateway,
                'payload' => $payload,
            ];
        });
    }

    public function verifyPayment(PaymentTransaction $transaction, array $payload, ?User $performedBy = null): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $payload, $performedBy): PaymentTransaction {
            if (in_array($transaction->verification_status, ['verified'], true)) {
                return $transaction;
            }

            $gateway = $transaction->gateway ?: $this->resolveGatewayForTransaction($transaction, $transaction->gateway_id);
            $implementation = $this->gatewayService->resolveGatewayImplementation($gateway);
            $result = $implementation->verifyPayment($transaction, $payload);

            $nextStatus = $result['status'] ?? $transaction->status;
            $nextVerificationStatus = $result['verification_status'] ?? $transaction->verification_status;

            if (($result['verified'] ?? false) === true) {
                $this->reconciliations->preventDuplicateSuccessfulReconciliation($transaction, $nextStatus);
            }

            $transaction = $this->transactions->update($transaction, [
                'gateway_order_id' => $result['gateway_order_id'] ?? $transaction->gateway_order_id,
                'gateway_payment_id' => $result['gateway_payment_id'] ?? $transaction->gateway_payment_id,
                'gateway_signature' => $result['gateway_signature'] ?? $transaction->gateway_signature,
                'upi_reference_no' => $result['upi_reference_no'] ?? $transaction->upi_reference_no,
                'status' => $nextStatus,
                'verification_status' => $nextVerificationStatus,
                'paid_at' => in_array($nextStatus, ['successful', 'manually_verified'], true) ? ($transaction->paid_at ?? now()) : $transaction->paid_at,
                'verified_at' => in_array($nextVerificationStatus, ['verified', 'manual_review'], true) ? now() : $transaction->verified_at,
                'verified_by' => $performedBy?->id ?? $transaction->verified_by,
                'failure_reason' => $nextStatus === 'failed' ? ($payload['failure_reason'] ?? 'Verification failed.') : null,
                'metadata' => array_merge($transaction->metadata ?? [], $result['metadata'] ?? []),
            ]);

            $this->reconciliations->reconcile(
                $transaction,
                'gateway_api',
                $transaction->status,
                $performedBy,
                'Payment verification completed.',
                ['idempotency_key' => 'verify:'.$transaction->transaction_no]
            );

            if (in_array($transaction->status, ['successful', 'manually_verified'], true)) {
                DB::afterCommit(fn () => event(new PaymentSuccessful($transaction)));
            }

            if ($transaction->status === 'failed') {
                DB::afterCommit(fn () => event(new PaymentFailed($transaction, $transaction->failure_reason)));
            }

            return $transaction;
        });
    }

    public function manualApprove(PaymentTransaction $transaction, array $payload, User $performedBy): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $payload, $performedBy): PaymentTransaction {
            $nextStatus = $payload['status'];
            $nextVerificationStatus = $payload['verification_status'];

            if (in_array($nextStatus, ['successful', 'manually_verified'], true)) {
                $this->reconciliations->preventDuplicateSuccessfulReconciliation($transaction, $nextStatus);
            }

            $transaction = $this->transactions->update($transaction, [
                'status' => $nextStatus,
                'verification_status' => $nextVerificationStatus,
                'verified_by' => $performedBy->id,
                'verified_at' => now(),
                'paid_at' => in_array($nextStatus, ['successful', 'manually_verified'], true) ? ($transaction->paid_at ?? now()) : $transaction->paid_at,
                'upi_reference_no' => $payload['upi_reference_no'] ?? $transaction->upi_reference_no,
                'failure_reason' => $nextStatus === 'failed' ? ($payload['remarks'] ?? 'Manual approval failed.') : null,
                'metadata' => array_merge($transaction->metadata ?? [], ['manual_approval' => $payload]),
            ]);

            $this->reconciliations->reconcile(
                $transaction,
                'manual',
                $transaction->status,
                $performedBy,
                $payload['remarks'] ?? 'Manual approval applied.',
                ['idempotency_key' => 'manual:'.$transaction->transaction_no]
            );

            if (in_array($transaction->status, ['successful', 'manually_verified'], true)) {
                DB::afterCommit(fn () => event(new PaymentSuccessful($transaction)));
            }

            if ($transaction->status === 'failed') {
                DB::afterCommit(fn () => event(new PaymentFailed($transaction, $transaction->failure_reason)));
            }

            return $transaction;
        });
    }

    public function cancel(PaymentTransaction $transaction, ?User $performedBy = null, ?string $reason = null): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $performedBy, $reason): PaymentTransaction {
            $transaction = $this->transactions->update($transaction, [
                'status' => 'cancelled',
                'failure_reason' => $reason,
            ]);

            $this->reconciliations->reconcile(
                $transaction,
                'manual',
                'cancelled',
                $performedBy,
                $reason ?: 'Transaction cancelled.',
                ['idempotency_key' => 'cancel:'.$transaction->transaction_no]
            );

            return $transaction;
        });
    }

    protected function resolveGatewayForTransaction(PaymentTransaction $transaction, ?int $gatewayId = null): PaymentGateway
    {
        if ($gatewayId) {
            return $this->gatewayService->findOrFail($gatewayId);
        }

        $gateway = $this->gatewayService->getActiveGatewayForTenant($transaction->provider, $transaction->school_id);

        if (! $gateway) {
            throw ValidationException::withMessages([
                'gateway' => ['No active payment gateway is configured for the selected provider.'],
            ]);
        }

        return $gateway;
    }

    protected function generateTransactionNo(int $schoolId): string
    {
        return 'TXN-'.$schoolId.'-'.Str::upper(Str::random(12));
    }
}
