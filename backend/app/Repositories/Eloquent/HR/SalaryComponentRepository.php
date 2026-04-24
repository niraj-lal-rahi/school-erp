<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\SalaryComponentData;
use App\Models\HR\SalaryComponent;
use App\Repositories\Contracts\HR\SalaryComponentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SalaryComponentRepository implements SalaryComponentRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return SalaryComponent::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($componentQuery) use ($search): void {
                    $componentQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['component_type'] ?? null, fn ($query, string $type) => $query->where('component_type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function create(SalaryComponentData $data): SalaryComponent
    {
        return SalaryComponent::create($data->attributes);
    }

    public function update(SalaryComponent $component, SalaryComponentData $data): SalaryComponent
    {
        $component->update($data->attributes);

        return $component->refresh();
    }

    public function delete(SalaryComponent $component): void
    {
        $component->delete();
    }
}
