<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FeeHeadData;
use App\Models\Finance\FeeHead;
use App\Repositories\Contracts\Finance\FeeHeadRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FeeHeadService
{
    public function __construct(
        protected FeeHeadRepositoryInterface $feeHeads,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->feeHeads->all($filters);
    }

    public function create(FeeHeadData $data): FeeHead
    {
        return DB::transaction(fn (): FeeHead => $this->feeHeads->create($data));
    }

    public function update(FeeHead $feeHead, FeeHeadData $data): FeeHead
    {
        return DB::transaction(fn (): FeeHead => $this->feeHeads->update($feeHead, $data));
    }

    public function delete(FeeHead $feeHead): void
    {
        DB::transaction(fn (): bool => $feeHead->delete());
    }
}
