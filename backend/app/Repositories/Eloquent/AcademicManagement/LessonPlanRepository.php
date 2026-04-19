<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\LessonPlan;
use App\Repositories\Contracts\AcademicManagement\LessonPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class LessonPlanRepository extends AbstractAcademicRepository implements LessonPlanRepositoryInterface
{
    protected array $searchable = ['title', 'topic'];
    protected array $filterable = ['academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status'];
    protected array $with = ['academicYear', 'term', 'schoolClass', 'section', 'subject', 'staff'];

    protected function model(): Model
    {
        return new LessonPlan();
    }
}
