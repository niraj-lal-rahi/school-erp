<?php

namespace App\Services\Payments;

use App\Models\Payments\PaymentReconciliation;
use App\Models\Payments\PaymentTransaction;
use App\Models\User;
use App\Repositories\Contracts\Payments\PaymentReconciliationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaymentReconciliationService
{
    public function __construct(
        protected PaymentReconciliationRepositoryInterface $reconciliations,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->reconciliations->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): PaymentReconciliation
    {
        return $this->reconciliations->findOrFail($id);
    }

    public function listByTransaction(int $transactionId): Collection
    {
        return $this->reconciliations->listByTransaction($transactionId);
    }

    public function reconcile(
        PaymentTransaction $transaction,
        string $source,
        string $newStatus,
        ?User $performedBy = null,
        ?string $remarks = null,
        array $metadata = []
    ): PaymentReconciliation {
        $latest = $transaction->reconciliations()
            ->latest('id')
            ->first();

        if ($latest && $latest->source === $source && $latest->new_status === $newStatus && ($latest->metadata['idempotency_key'] ?? null) === ($metadata['idempotency_key'] ?? null)) {
            return $latest;
        }

        return $this->reconciliations->create([
            'school_id' => $transaction->school_id,
            'transaction_id' => $transaction->id,
            'source' => $source,
            'old_status' => $transaction->status,
            'new_status' => $newStatus,
            'reconciled_by' => $performedBy?->id,
            'reconciled_at' => now(),
            'remarks' => $remarks,
            'metadata' => $metadata,
        ]);
    }

    public function preventDuplicateSuccessfulReconciliation(PaymentTransaction $transaction, string $newStatus): void
    {
        if ($newStatus !== 'successful' && $newStatus !== 'manually_verified') {
            return;
        }

        if (in_array($transaction->status, ['successful', 'manually_verified', 'refunded'], true)) {
            throw ValidationException::withMessages([
                'transaction' => ['This transaction has already been reconciled successfully.'],
            ]);
        }
    }
}
