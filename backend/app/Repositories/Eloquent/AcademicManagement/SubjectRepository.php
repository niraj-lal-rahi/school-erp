<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\Subject;
use App\Repositories\Contracts\AcademicManagement\SubjectRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SubjectRepository extends AbstractAcademicRepository implements SubjectRepositoryInterface
{
    protected array $searchable = ['name', 'code'];
    protected array $filterable = ['type', 'status'];

    protected function model(): Model
    {
        return new Subject();
    }
}
