<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FeeInvoiceData;
use App\Models\Finance\FeeInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FeeInvoiceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): FeeInvoice;

    public function create(FeeInvoiceData $data, array $items): FeeInvoice;

    public function update(FeeInvoice $invoice, FeeInvoiceData $data, array $items): FeeInvoice;

    public function delete(FeeInvoice $invoice): void;

    public function nextInvoiceNumber(int $schoolId): string;

    public function linkedInstallmentIds(array $installmentIds, ?int $ignoreInvoiceId = null): Collection;
}
