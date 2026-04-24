<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FeeStructureData;
use App\Models\Finance\FeeStructure;
use App\Repositories\Contracts\Finance\FeeStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FeeStructureRepository implements FeeStructureRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return FeeStructure::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($feeStructureQuery) use ($search): void {
                    $feeStructureQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['school_class_id'] ?? null, fn ($query, int|string $value) => $query->where('school_class_id', $value))
            ->when($filters['section_id'] ?? null, fn ($query, int|string $value) => $query->where('section_id', $value))
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->with(['academicYear', 'schoolClass', 'section', 'items.feeHead'])
            ->orderBy('name')
            ->get();
    }

    public function create(FeeStructureData $data): FeeStructure
    {
        $feeStructure = FeeStructure::create($data->attributes);

        $this->syncItems($feeStructure, $data);

        return $this->findOrFail($feeStructure->id);
    }

    public function update(FeeStructure $feeStructure, FeeStructureData $data): FeeStructure
    {
        $feeStructure->update($data->attributes);
        $this->syncItems($feeStructure, $data);

        return $this->findOrFail($feeStructure->id);
    }

    public function delete(FeeStructure $feeStructure): void
    {
        $feeStructure->delete();
    }

    protected function syncItems(FeeStructure $feeStructure, FeeStructureData $data): void
    {
        $feeStructure->items()->delete();

        $items = collect($data->items)
            ->map(fn (array $item): array => [
                'school_id' => $feeStructure->school_id,
                'fee_head_id' => $item['fee_head_id'],
                'amount' => $item['amount'],
                'due_frequency' => $item['due_frequency'],
                'due_day' => $item['due_day'] ?? null,
                'sort_order' => $item['sort_order'] ?? null,
            ])->all();

        if ($items !== []) {
            $feeStructure->items()->createMany($items);
        }
    }

    protected function findOrFail(int $id): FeeStructure
    {
        return FeeStructure::query()
            ->with(['academicYear', 'schoolClass', 'section', 'items.feeHead'])
            ->findOrFail($id);
    }
}
