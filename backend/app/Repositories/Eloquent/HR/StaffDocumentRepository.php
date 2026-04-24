<?php

namespace App\Repositories\Eloquent\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffDocument;
use App\Repositories\Contracts\HR\StaffDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StaffDocumentRepository implements StaffDocumentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $documentQuery) use ($search): void {
                    $documentQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhereHas('staff', function (Builder $staffQuery) use ($search): void {
                            $staffQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('employee_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('staff_id', $value))
            ->when($filters['document_type'] ?? null, fn (Builder $query, string $value) => $query->where('document_type', $value))
            ->when($filters['verification_status'] ?? null, fn (Builder $query, string $value) => $query->where('verification_status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StaffDocument
    {
        return $this->query()->findOrFail($id);
    }

    public function create(Staff $staff, array $attributes): StaffDocument
    {
        return $staff->documents()->create($attributes);
    }

    public function update(StaffDocument $document, array $attributes): StaffDocument
    {
        $document->update($attributes);

        return $this->findOrFail($document->id);
    }

    public function delete(StaffDocument $document): void
    {
        $document->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->query()->where('staff_id', $staff->id)->get();
    }

    protected function query(): Builder
    {
        return StaffDocument::query()->with(['staff', 'uploader']);
    }
}
