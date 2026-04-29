<?php

namespace App\Repositories\Eloquent\Examination;

use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use App\Repositories\Contracts\Examination\GradingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GradingRepository implements GradingRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $gradingQuery) use ($search): void {
                    $gradingQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['result_status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['grading_type'] ?? null, fn (Builder $query, string $value) => $query->where('grading_type', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findSystemOrFail(int $id): GradingSystem
    {
        return $this->query()->findOrFail($id);
    }

    public function createSystem(array $attributes): GradingSystem
    {
        $gradingSystem = GradingSystem::create($attributes);

        return $this->findSystemOrFail($gradingSystem->id);
    }

    public function updateSystem(GradingSystem $gradingSystem, array $attributes): GradingSystem
    {
        $gradingSystem->update($attributes);

        return $this->findSystemOrFail($gradingSystem->id);
    }

    public function deleteSystem(GradingSystem $gradingSystem): void
    {
        $gradingSystem->delete();
    }

    public function createScale(array $attributes): GradeScale
    {
        $gradeScale = GradeScale::create($attributes);

        return GradeScale::query()
            ->with(['gradingSystem'])
            ->findOrFail($gradeScale->id);
    }

    public function scalesForSystem(int $gradingSystemId): Collection
    {
        return GradeScale::query()
            ->where('grading_system_id', $gradingSystemId)
            ->orderBy('min_percentage')
            ->get();
    }

    public function deleteScale(GradeScale $gradeScale): void
    {
        $gradeScale->delete();
    }

    protected function query(): Builder
    {
        return GradingSystem::query()
            ->with(['gradeScales'])
            ->withCount(['gradeScales']);
    }
}
