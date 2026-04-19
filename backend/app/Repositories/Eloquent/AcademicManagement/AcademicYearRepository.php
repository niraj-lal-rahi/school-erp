<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicManagement\AcademicYearRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class AcademicYearRepository extends AbstractAcademicRepository implements AcademicYearRepositoryInterface
{
    protected array $searchable = ['name', 'code'];
    protected array $filterable = ['status', 'is_active'];
    protected array $with = ['creator', 'updater'];

    protected function model(): Model
    {
        return new AcademicYear();
    }
}
