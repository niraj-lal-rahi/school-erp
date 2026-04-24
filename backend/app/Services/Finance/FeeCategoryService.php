<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FeeCategoryData;
use App\Models\Finance\FeeCategory;
use App\Repositories\Contracts\Finance\FeeCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FeeCategoryService
{
    public function __construct(
        protected FeeCategoryRepositoryInterface $feeCategories,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->feeCategories->all($filters);
    }

    public function create(FeeCategoryData $data): FeeCategory
    {
        return DB::transaction(fn (): FeeCategory => $this->feeCategories->create($data));
    }

    public function update(FeeCategory $feeCategory, FeeCategoryData $data): FeeCategory
    {
        return DB::transaction(fn (): FeeCategory => $this->feeCategories->update($feeCategory, $data));
    }

    public function delete(FeeCategory $feeCategory): void
    {
        DB::transaction(fn (): bool => $feeCategory->delete());
    }
}
