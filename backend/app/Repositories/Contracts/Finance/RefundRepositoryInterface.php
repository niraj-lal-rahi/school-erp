<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\RefundData;
use App\Models\Finance\Refund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RefundRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(RefundData $data): Refund;

    public function update(Refund $refund, array $attributes): Refund;

    public function delete(Refund $refund): void;

    public function nextRefundNumber(int $schoolId): string;

    public function totalProcessedAmountForPayment(int $paymentId, ?int $ignoreRefundId = null): float;
}
