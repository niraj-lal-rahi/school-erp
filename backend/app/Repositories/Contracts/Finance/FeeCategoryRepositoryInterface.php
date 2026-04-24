<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FeeCategoryData;
use App\Models\Finance\FeeCategory;
use Illuminate\Database\Eloquent\Collection;

interface FeeCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(FeeCategoryData $data): FeeCategory;

    public function update(FeeCategory $feeCategory, FeeCategoryData $data): FeeCategory;

    public function delete(FeeCategory $feeCategory): void;
}
