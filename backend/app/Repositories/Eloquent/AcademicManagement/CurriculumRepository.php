<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\Curriculum;
use App\Repositories\Contracts\AcademicManagement\CurriculumRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CurriculumRepository extends AbstractAcademicRepository implements CurriculumRepositoryInterface
{
    protected array $searchable = ['title'];
    protected array $filterable = ['academic_year_id', 'school_class_id', 'subject_id', 'academic_term_id', 'status'];
    protected array $with = ['academicYear', 'schoolClass', 'subject', 'term'];

    protected function model(): Model
    {
        return new Curriculum();
    }
}
