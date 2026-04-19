<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\GradingStructure;
use App\Repositories\Contracts\AcademicManagement\GradingStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class GradingStructureRepository extends AbstractAcademicRepository implements GradingStructureRepositoryInterface
{
    protected array $searchable = ['name'];
    protected array $filterable = ['academic_year_id', 'status'];
    protected array $with = ['academicYear', 'scaleItems'];

    protected function model(): Model
    {
        return new GradingStructure();
    }
}
