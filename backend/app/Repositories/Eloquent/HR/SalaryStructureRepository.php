<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\SalaryStructureData;
use App\Models\HR\SalaryStructure;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\SalaryStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SalaryStructureRepository implements SalaryStructureRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return SalaryStructure::query()
            ->with(['staff', 'items.salaryComponent'])
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('effective_from')
            ->get();
    }

    public function findOrFail(int $id): SalaryStructure
    {
        return SalaryStructure::query()->with(['staff', 'items.salaryComponent'])->findOrFail($id);
    }

    public function create(Staff $staff, SalaryStructureData $data): SalaryStructure
    {
        $structure = $staff->salaryStructures()->create($data->attributes + ['school_id' => $staff->school_id]);
        $structure->items()->createMany(
            collect($data->items)->map(fn (array $item): array => [
                ...$item,
                'school_id' => $staff->school_id,
            ])->all()
        );

        return $this->findOrFail($structure->id);
    }

    public function update(SalaryStructure $structure, SalaryStructureData $data): SalaryStructure
    {
        $structure->update($data->attributes);

        if ($data->items !== []) {
            $structure->items()->delete();
            $structure->items()->createMany(
                collect($data->items)->map(fn (array $item): array => [
                    ...$item,
                    'school_id' => $structure->school_id,
                ])->all()
            );
        }

        return $this->findOrFail($structure->id);
    }

    public function delete(SalaryStructure $structure): void
    {
        $structure->delete();
    }

    public function activeForPayrollMonth(int $schoolId, int $month, int $year): Collection
    {
        $periodStart = sprintf('%04d-%02d-01', $year, $month);
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        return SalaryStructure::query()
            ->with(['staff', 'items.salaryComponent'])
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereDate('effective_from', '<=', $periodEnd)
            ->where(function ($query) use ($periodStart): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $periodStart);
            })
            ->get();
    }
}
