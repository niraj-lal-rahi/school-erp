<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\LedgerAccountData;
use App\Models\Finance\LedgerAccount;
use Illuminate\Database\Eloquent\Collection;

interface LedgerAccountRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(LedgerAccountData $data): LedgerAccount;

    public function update(LedgerAccount $ledgerAccount, LedgerAccountData $data): LedgerAccount;

    public function delete(LedgerAccount $ledgerAccount): void;
}
