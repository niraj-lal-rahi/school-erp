<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FineRuleData;
use App\Models\Finance\FineRule;
use App\Repositories\Contracts\Finance\FineRuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FineRuleService
{
    public function __construct(
        protected FineRuleRepositoryInterface $fineRules,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->fineRules->all($filters);
    }

    public function create(FineRuleData $data): FineRule
    {
        return DB::transaction(fn (): FineRule => $this->fineRules->create($data));
    }

    public function update(FineRule $fineRule, FineRuleData $data): FineRule
    {
        return DB::transaction(fn (): FineRule => $this->fineRules->update($fineRule, $data));
    }

    public function delete(FineRule $fineRule): void
    {
        DB::transaction(fn (): bool => tap($fineRule)->delete());
    }
}
