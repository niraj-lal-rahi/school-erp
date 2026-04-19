<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\SchoolClass;
use App\Repositories\Contracts\AcademicManagement\SchoolClassRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SchoolClassRepository extends AbstractAcademicRepository implements SchoolClassRepositoryInterface
{
    protected array $searchable = ['name', 'code'];
    protected array $filterable = ['academic_year_id', 'status'];
    protected array $with = ['academicYear', 'sections'];

    protected function model(): Model
    {
        return new SchoolClass();
    }
}
