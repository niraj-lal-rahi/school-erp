<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FeeHeadData;
use App\Models\Finance\FeeHead;
use Illuminate\Database\Eloquent\Collection;

interface FeeHeadRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(FeeHeadData $data): FeeHead;

    public function update(FeeHead $feeHead, FeeHeadData $data): FeeHead;

    public function delete(FeeHead $feeHead): void;
}
