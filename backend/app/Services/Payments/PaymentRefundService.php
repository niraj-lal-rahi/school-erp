<?php

namespace App\Services\Payments;

use App\Events\Payments\PaymentRefunded;
use App\Contracts\Payments\SupportsRefundInterface;
use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;
use App\Models\User;
use App\Repositories\Contracts\Payments\PaymentRefundRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentRefundService
{
    public function __construct(
        protected PaymentRefundRepositoryInterface $refunds,
        protected PaymentGatewayService $gateways,
        protected PaymentReconciliationService $reconciliations,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->refunds->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): PaymentRefund
    {
        return $this->refunds->findOrFail($id);
    }

    public function requestRefund(PaymentTransaction $transaction, array $attributes, ?User $performedBy = null): PaymentRefund
    {
        return DB::transaction(function () use ($transaction, $attributes, $performedBy): PaymentRefund {
            return $this->refunds->create([
                'school_id' => $transaction->school_id,
                'transaction_id' => $transaction->id,
                'refund_no' => $attributes['refund_no'] ?? $this->generateRefundNo($transaction->school_id),
                'amount' => $attributes['amount'],
                'reason' => $attributes['reason'] ?? null,
                'status' => 'requested',
                'requested_by' => $performedBy?->id ?? ($attributes['requested_by'] ?? null),
                'metadata' => $attributes['metadata'] ?? [],
            ]);
        });
    }

    public function processRefund(PaymentRefund $refund): PaymentRefund
    {
        return DB::transaction(function () use ($refund): PaymentRefund {
            $transaction = $refund->transaction;

            if (! $transaction || ! $transaction->gateway_id) {
                throw ValidationException::withMessages([
                    'transaction' => ['Refund cannot be processed without a linked transaction gateway.'],
                ]);
            }

            $implementation = $this->gateways->resolveGatewayImplementation($transaction->gateway);

            if (! $implementation instanceof SupportsRefundInterface) {
                throw ValidationException::withMessages([
                    'gateway' => ['The selected gateway does not support refunds.'],
                ]);
            }

            $refund = $this->refunds->update($refund, [
                'status' => 'processing',
            ]);

            $response = $implementation->refund($transaction, [
                'amount' => $refund->amount,
                'reason' => $refund->reason,
            ]);

            $refund = $this->refunds->update($refund, $implementation->mapRefundResponse($refund, $response) + [
                'processed_at' => now(),
            ]);

            if ($refund->status === 'processing' || $response['status'] === 'successful') {
                $refund = $this->refunds->update($refund, [
                    'status' => $response['status'] === 'failed' ? 'failed' : 'successful',
                    'processed_at' => now(),
                ]);
            }

            if ($refund->status === 'successful') {
                $transactionStatus = (float) $refund->amount >= (float) $transaction->amount ? 'refunded' : $transaction->status;
                $this->reconciliations->reconcile(
                    $transaction,
                    'gateway_api',
                    $transactionStatus,
                    null,
                    'Refund processed successfully.',
                    ['refund_id' => $refund->id, 'idempotency_key' => 'refund:'.$refund->refund_no]
                );

                if ($transactionStatus === 'refunded') {
                    $transaction->update(['status' => 'refunded']);
                }

                DB::afterCommit(fn () => event(new PaymentRefunded($refund, $transaction)));
            }

            return $refund;
        });
    }

    protected function generateRefundNo(int $schoolId): string
    {
        return 'RFD-'.$schoolId.'-'.Str::upper(Str::random(10));
    }
}
