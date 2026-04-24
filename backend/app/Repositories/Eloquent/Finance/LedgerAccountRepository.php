<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\LedgerAccountData;
use App\Models\Finance\LedgerAccount;
use App\Repositories\Contracts\Finance\LedgerAccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LedgerAccountRepository implements LedgerAccountRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return LedgerAccount::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($accountQuery) use ($search): void {
                    $accountQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['account_type'] ?? null, fn ($query, string $value) => $query->where('account_type', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->with(['parent'])
            ->withCount('entries')
            ->orderBy('name')
            ->get();
    }

    public function create(LedgerAccountData $data): LedgerAccount
    {
        return LedgerAccount::create($data->attributes);
    }

    public function update(LedgerAccount $ledgerAccount, LedgerAccountData $data): LedgerAccount
    {
        $ledgerAccount->update($data->attributes);

        return $ledgerAccount->refresh()->load(['parent'])->loadCount('entries');
    }

    public function delete(LedgerAccount $ledgerAccount): void
    {
        $ledgerAccount->delete();
    }
}
