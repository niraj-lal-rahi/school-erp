<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\DiscountTypeData;
use App\Models\Finance\DiscountType;
use App\Repositories\Contracts\Finance\DiscountTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DiscountTypeRepository implements DiscountTypeRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return DiscountType::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($discountQuery) use ($search): void {
                    $discountQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function create(DiscountTypeData $data): DiscountType
    {
        return DiscountType::create($data->attributes);
    }

    public function update(DiscountType $discountType, DiscountTypeData $data): DiscountType
    {
        $discountType->update($data->attributes);

        return $discountType->refresh();
    }

    public function delete(DiscountType $discountType): void
    {
        $discountType->delete();
    }
}
