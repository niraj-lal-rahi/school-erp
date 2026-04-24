<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\DesignationData;
use App\Models\HR\Designation;
use App\Repositories\Contracts\HR\DesignationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DesignationRepository implements DesignationRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return Designation::query()
            ->with('department')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($designationQuery) use ($search): void {
                    $designationQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['department_id'] ?? null, fn ($query, int|string $departmentId) => $query->where('department_id', $departmentId))
            ->withCount('staff')
            ->orderBy('name')
            ->get();
    }

    public function create(DesignationData $data): Designation
    {
        return Designation::create($data->attributes);
    }

    public function update(Designation $designation, DesignationData $data): Designation
    {
        $designation->update($data->attributes);

        return $designation->refresh()->load(['department'])->loadCount('staff');
    }

    public function delete(Designation $designation): void
    {
        $designation->delete();
    }
}
