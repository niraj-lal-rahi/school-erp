<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\LedgerEntryData;
use App\Models\Finance\LedgerEntry;
use App\Repositories\Contracts\Finance\LedgerEntryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class LedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $entryQuery) use ($search): void {
                    $entryQuery->where('source_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['ledger_account_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('ledger_account_id', $value))
            ->when($filters['entry_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('entry_date', '>=', $value))
            ->when($filters['entry_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('entry_date', '<=', $value))
            ->latest('entry_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(LedgerEntryData $data): LedgerEntry
    {
        return LedgerEntry::create($data->attributes);
    }

    public function update(LedgerEntry $ledgerEntry, LedgerEntryData $data): LedgerEntry
    {
        $ledgerEntry->update($data->attributes);

        return $this->query()->findOrFail($ledgerEntry->id);
    }

    public function delete(LedgerEntry $ledgerEntry): void
    {
        $ledgerEntry->delete();
    }

    protected function query(): Builder
    {
        return LedgerEntry::query()->with('ledgerAccount');
    }
}
