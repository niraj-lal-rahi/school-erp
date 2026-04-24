<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\DiscountTypeData;
use App\Models\Finance\DiscountType;
use Illuminate\Database\Eloquent\Collection;

interface DiscountTypeRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(DiscountTypeData $data): DiscountType;

    public function update(DiscountType $discountType, DiscountTypeData $data): DiscountType;

    public function delete(DiscountType $discountType): void;
}
