<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\AcademicTerm;
use App\Repositories\Contracts\AcademicManagement\AcademicTermRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class AcademicTermRepository extends AbstractAcademicRepository implements AcademicTermRepositoryInterface
{
    protected array $searchable = ['name', 'code'];
    protected array $filterable = ['academic_year_id', 'status'];
    protected array $with = ['academicYear'];

    protected function model(): Model
    {
        return new AcademicTerm();
    }
}
