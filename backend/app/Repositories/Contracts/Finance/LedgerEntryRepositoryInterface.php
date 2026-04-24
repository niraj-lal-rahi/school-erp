<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\LedgerEntryData;
use App\Models\Finance\LedgerEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LedgerEntryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(LedgerEntryData $data): LedgerEntry;

    public function update(LedgerEntry $ledgerEntry, LedgerEntryData $data): LedgerEntry;

    public function delete(LedgerEntry $ledgerEntry): void;
}
