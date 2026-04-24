<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FeeCategoryData;
use App\Models\Finance\FeeCategory;
use App\Repositories\Contracts\Finance\FeeCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FeeCategoryRepository implements FeeCategoryRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return FeeCategory::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($feeCategoryQuery) use ($search): void {
                    $feeCategoryQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->withCount('feeHeads')
            ->orderBy('name')
            ->get();
    }

    public function create(FeeCategoryData $data): FeeCategory
    {
        return FeeCategory::create($data->attributes);
    }

    public function update(FeeCategory $feeCategory, FeeCategoryData $data): FeeCategory
    {
        $feeCategory->update($data->attributes);

        return $feeCategory->refresh()->loadCount('feeHeads');
    }

    public function delete(FeeCategory $feeCategory): void
    {
        $feeCategory->delete();
    }
}
