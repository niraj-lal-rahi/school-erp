<?php

namespace App\Repositories\Eloquent\Payments;

use App\Models\Payments\PaymentTransaction;
use App\Repositories\Contracts\Payments\PaymentTransactionRepositoryInterface;
use App\Support\Pagination\PaginationDefaults;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate(PaginationDefaults::resolvePerPage($perPage));
    }

    public function findOrFail(int $id): PaymentTransaction
    {
        return $this->detailQuery()->withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): PaymentTransaction
    {
        $transaction = PaymentTransaction::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($transaction->id);
    }

    public function update(PaymentTransaction $transaction, array $attributes): PaymentTransaction
    {
        $transaction->update($attributes);

        return $this->findOrFail($transaction->id);
    }

    public function findByGatewayPaymentId(string $gatewayPaymentId, ?int $schoolId = null): ?PaymentTransaction
    {
        return $this->baseQuery()
            ->when($schoolId !== null, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->first();
    }

    public function findByGatewayOrderId(string $gatewayOrderId, ?int $schoolId = null): ?PaymentTransaction
    {
        return $this->baseQuery()
            ->when($schoolId !== null, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->where('gateway_order_id', $gatewayOrderId)
            ->first();
    }

    public function findByTransactionNo(string $transactionNo, int $schoolId): ?PaymentTransaction
    {
        return $this->baseQuery()
            ->where('school_id', $schoolId)
            ->where('transaction_no', $transactionNo)
            ->first();
    }

    public function listByPayable(string $payableType, ?int $payableId, int $schoolId): Collection
    {
        return $this->baseQuery()
            ->where('school_id', $schoolId)
            ->where('payable_type', $payableType)
            ->when($payableId !== null, fn (Builder $query) => $query->where('payable_id', $payableId))
            ->latest('id')
            ->get();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->listQuery()
            ->when($filters['school_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['provider'] ?? null, fn (Builder $query, string $value) => $query->where('provider', $value))
            ->when($filters['payment_method'] ?? null, fn (Builder $query, string $value) => $query->where('payment_method', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['verification_status'] ?? null, fn (Builder $query, string $value) => $query->where('verification_status', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, $value) => $query->where('student_id', $value))
            ->when($filters['payable_type'] ?? null, fn (Builder $query, string $value) => $query->where('payable_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }

    protected function baseQuery(): Builder
    {
        return PaymentTransaction::withoutGlobalScopes()->select([
            'payment_transactions.id',
            'payment_transactions.school_id',
            'payment_transactions.transaction_no',
            'payment_transactions.payable_type',
            'payment_transactions.payable_id',
            'payment_transactions.student_id',
            'payment_transactions.tenant_subscription_id',
            'payment_transactions.gateway_id',
            'payment_transactions.provider',
            'payment_transactions.payment_method',
            'payment_transactions.amount',
            'payment_transactions.currency',
            'payment_transactions.gateway_order_id',
            'payment_transactions.gateway_payment_id',
            'payment_transactions.upi_vpa',
            'payment_transactions.upi_reference_no',
            'payment_transactions.upi_qr_payload',
            'payment_transactions.status',
            'payment_transactions.verification_status',
            'payment_transactions.paid_at',
            'payment_transactions.verified_at',
            'payment_transactions.verified_by',
            'payment_transactions.failure_reason',
            'payment_transactions.metadata',
            'payment_transactions.created_at',
            'payment_transactions.updated_at',
            'payment_transactions.deleted_at',
        ]);
    }

    protected function listQuery(): Builder
    {
        return $this->baseQuery()->with([
            'gateway:id,name,code,provider,mode,status',
            'student:id,full_name,admission_no,roll_no',
            'tenantSubscription:id,subscription_plan_id,billing_cycle,status',
            'verifier:id,name,email',
            'upiPaymentRequest:id,transaction_id,upi_vpa,amount,currency,status,expires_at',
        ]);
    }

    protected function detailQuery(): Builder
    {
        return $this->baseQuery()->with([
            'gateway',
            'student',
            'tenantSubscription.subscriptionPlan',
            'verifier',
            'refunds',
            'reconciliations',
            'upiPaymentRequest',
        ]);
    }
}
