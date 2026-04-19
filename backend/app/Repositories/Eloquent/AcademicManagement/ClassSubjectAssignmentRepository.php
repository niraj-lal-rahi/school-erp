<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Repositories\Contracts\AcademicManagement\ClassSubjectAssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ClassSubjectAssignmentRepository extends AbstractAcademicRepository implements ClassSubjectAssignmentRepositoryInterface
{
    protected array $searchable = [];
    protected array $filterable = ['academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'status'];
    protected array $with = ['academicYear', 'schoolClass', 'section', 'subject'];

    protected function model(): Model
    {
        return new ClassSubjectAssignment();
    }
}
