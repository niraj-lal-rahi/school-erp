<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FeeHeadData;
use App\Models\Finance\FeeHead;
use App\Repositories\Contracts\Finance\FeeHeadRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FeeHeadRepository implements FeeHeadRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return FeeHead::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($feeHeadQuery) use ($search): void {
                    $feeHeadQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['fee_category_id'] ?? null, fn ($query, int|string $feeCategoryId) => $query->where('fee_category_id', $feeCategoryId))
            ->with('feeCategory')
            ->orderBy('name')
            ->get();
    }

    public function create(FeeHeadData $data): FeeHead
    {
        return FeeHead::create($data->attributes);
    }

    public function update(FeeHead $feeHead, FeeHeadData $data): FeeHead
    {
        $feeHead->update($data->attributes);

        return $feeHead->refresh()->load('feeCategory');
    }

    public function delete(FeeHead $feeHead): void
    {
        $feeHead->delete();
    }
}
