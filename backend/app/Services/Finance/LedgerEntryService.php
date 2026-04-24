<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\LedgerEntryData;
use App\Models\Finance\LedgerEntry;
use App\Repositories\Contracts\Finance\LedgerEntryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LedgerEntryService
{
    public function __construct(
        protected LedgerEntryRepositoryInterface $ledgerEntries,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->ledgerEntries->paginate($filters, $perPage);
    }

    public function create(LedgerEntryData $data): LedgerEntry
    {
        $this->guardAmounts($data->attributes);

        return DB::transaction(fn (): LedgerEntry => $this->ledgerEntries->create($data));
    }

    public function update(LedgerEntry $ledgerEntry, LedgerEntryData $data): LedgerEntry
    {
        $this->guardAmounts($data->attributes);

        return DB::transaction(fn (): LedgerEntry => $this->ledgerEntries->update($ledgerEntry, $data));
    }

    public function delete(LedgerEntry $ledgerEntry): void
    {
        DB::transaction(fn (): bool => tap($ledgerEntry)->delete());
    }

    protected function guardAmounts(array $attributes): void
    {
        $debit = (float) ($attributes['debit'] ?? 0);
        $credit = (float) ($attributes['credit'] ?? 0);

        if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
            throw ValidationException::withMessages([
                'amount' => 'A ledger entry must have either a debit or a credit amount.',
            ]);
        }
    }
}
