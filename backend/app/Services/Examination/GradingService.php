<?php

namespace App\Services\Examination;

use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use App\Repositories\Eloquent\Examination\GradingRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradingService
{
    public function __construct(
        protected GradingRepository $grading,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->grading->paginate($filters, $perPage);
    }

    public function findSystemOrFail(int $id): GradingSystem
    {
        return $this->grading->findSystemOrFail($id);
    }

    public function createSystem(array $attributes): GradingSystem
    {
        return DB::transaction(fn (): GradingSystem => $this->grading->createSystem($attributes));
    }

    public function updateSystem(GradingSystem $gradingSystem, array $attributes): GradingSystem
    {
        return DB::transaction(fn (): GradingSystem => $this->grading->updateSystem($gradingSystem, $attributes));
    }

    public function deleteSystem(GradingSystem $gradingSystem): void
    {
        DB::transaction(function () use ($gradingSystem): void {
            $this->grading->deleteSystem($gradingSystem);
        });
    }

    public function addScale(GradingSystem $gradingSystem, array $attributes): GradeScale
    {
        return DB::transaction(fn (): GradeScale => $this->grading->createScale([
            ...$attributes,
            'school_id' => $gradingSystem->school_id,
            'grading_system_id' => $gradingSystem->id,
        ]));
    }

    public function removeScale(GradeScale $gradeScale): void
    {
        DB::transaction(function () use ($gradeScale): void {
            $this->grading->deleteScale($gradeScale);
        });
    }

    public function activeSystem(?int $gradingSystemId = null, ?int $schoolId = null): GradingSystem
    {
        if ($gradingSystemId) {
            $system = $this->grading->findSystemOrFail($gradingSystemId);

            if ($system->status !== 'active') {
                throw ValidationException::withMessages([
                    'grading_system_id' => 'The selected grading system is inactive.',
                ]);
            }

            return $system;
        }

        $query = GradingSystem::query()->where('status', 'active')->orderBy('id');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $system = $query->first();

        if (! $system) {
            throw ValidationException::withMessages([
                'grading_system_id' => 'No active grading system is configured.',
            ]);
        }

        return $system;
    }

    public function mapPercentage(float $percentage, ?GradingSystem $gradingSystem = null, ?int $gradingSystemId = null, ?int $schoolId = null): array
    {
        $system = $gradingSystem ?? $this->activeSystem($gradingSystemId, $schoolId);
        $scale = $this->matchScale($system, $percentage);

        return [
            'grading_system' => $system,
            'grade' => $scale?->grade_label,
            'gpa' => $scale?->grade_point !== null ? (float) $scale->grade_point : null,
            'is_pass' => $system->pass_percentage === null
                ? true
                : $percentage >= (float) $system->pass_percentage,
        ];
    }

    public function scalesForSystem(GradingSystem $gradingSystem): Collection
    {
        return $this->grading->scalesForSystem($gradingSystem->id);
    }

    protected function matchScale(GradingSystem $gradingSystem, float $percentage): ?GradeScale
    {
        return $this->grading->scalesForSystem($gradingSystem->id)
            ->first(function (GradeScale $scale) use ($percentage): bool {
                return $percentage >= (float) $scale->min_percentage
                    && $percentage <= (float) $scale->max_percentage;
            });
    }
}
