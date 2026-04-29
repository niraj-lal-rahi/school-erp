<?php

namespace App\Repositories\Contracts\Examination;

use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface GradingRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findSystemOrFail(int $id): GradingSystem;

    public function createSystem(array $attributes): GradingSystem;

    public function updateSystem(GradingSystem $gradingSystem, array $attributes): GradingSystem;

    public function deleteSystem(GradingSystem $gradingSystem): void;

    public function createScale(array $attributes): GradeScale;

    public function scalesForSystem(int $gradingSystemId): Collection;

    public function deleteScale(GradeScale $gradeScale): void;
}
