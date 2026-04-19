<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\HomeworkAssignment;
use App\Repositories\Contracts\AcademicManagement\HomeworkAssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class HomeworkAssignmentRepository extends AbstractAcademicRepository implements HomeworkAssignmentRepositoryInterface
{
    protected array $searchable = ['title'];
    protected array $filterable = ['academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status'];
    protected array $with = ['academicYear', 'term', 'schoolClass', 'section', 'subject', 'staff'];

    protected function model(): Model
    {
        return new HomeworkAssignment();
    }
}
