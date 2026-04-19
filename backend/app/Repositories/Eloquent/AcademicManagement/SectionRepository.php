<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\Section;
use App\Repositories\Contracts\AcademicManagement\SectionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class SectionRepository extends AbstractAcademicRepository implements SectionRepositoryInterface
{
    protected array $searchable = ['name', 'code'];
    protected array $filterable = ['school_class_id', 'status'];
    protected array $with = ['schoolClass.academicYear'];

    protected function model(): Model
    {
        return new Section();
    }
}
