<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\DiscountTypeData;
use App\Models\Finance\DiscountType;
use App\Repositories\Contracts\Finance\DiscountTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DiscountTypeService
{
    public function __construct(
        protected DiscountTypeRepositoryInterface $discountTypes,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->discountTypes->all($filters);
    }

    public function create(DiscountTypeData $data): DiscountType
    {
        return DB::transaction(fn (): DiscountType => $this->discountTypes->create($data));
    }

    public function update(DiscountType $discountType, DiscountTypeData $data): DiscountType
    {
        return DB::transaction(fn (): DiscountType => $this->discountTypes->update($discountType, $data));
    }

    public function delete(DiscountType $discountType): void
    {
        DB::transaction(fn (): bool => tap($discountType)->delete());
    }
}
