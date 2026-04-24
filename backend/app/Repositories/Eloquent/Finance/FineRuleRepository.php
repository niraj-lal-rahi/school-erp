<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FineRuleData;
use App\Models\Finance\FineRule;
use App\Repositories\Contracts\Finance\FineRuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FineRuleRepository implements FineRuleRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return FineRule::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($fineQuery) use ($search): void {
                    $fineQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->with('feeHead')
            ->orderBy('name')
            ->get();
    }

    public function create(FineRuleData $data): FineRule
    {
        return FineRule::create($data->attributes);
    }

    public function update(FineRule $fineRule, FineRuleData $data): FineRule
    {
        $fineRule->update($data->attributes);

        return $fineRule->refresh()->load('feeHead');
    }

    public function delete(FineRule $fineRule): void
    {
        $fineRule->delete();
    }
}
