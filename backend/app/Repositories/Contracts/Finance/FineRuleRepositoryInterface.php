<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FineRuleData;
use App\Models\Finance\FineRule;
use Illuminate\Database\Eloquent\Collection;

interface FineRuleRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(FineRuleData $data): FineRule;

    public function update(FineRule $fineRule, FineRuleData $data): FineRule;

    public function delete(FineRule $fineRule): void;
}
