<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\LedgerAccountData;
use App\Models\Finance\LedgerAccount;
use App\Repositories\Contracts\Finance\LedgerAccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LedgerAccountService
{
    public function __construct(
        protected LedgerAccountRepositoryInterface $ledgerAccounts,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->ledgerAccounts->all($filters);
    }

    public function create(LedgerAccountData $data): LedgerAccount
    {
        return DB::transaction(fn (): LedgerAccount => $this->ledgerAccounts->create($data));
    }

    public function update(LedgerAccount $ledgerAccount, LedgerAccountData $data): LedgerAccount
    {
        if ($ledgerAccount->id === ($data->attributes['parent_id'] ?? null)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A ledger account cannot be its own parent.',
            ]);
        }

        return DB::transaction(fn (): LedgerAccount => $this->ledgerAccounts->update($ledgerAccount, $data));
    }

    public function delete(LedgerAccount $ledgerAccount): void
    {
        if ($ledgerAccount->children()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Ledger accounts with child accounts cannot be deleted.',
            ]);
        }

        DB::transaction(fn (): bool => tap($ledgerAccount)->delete());
    }
}
