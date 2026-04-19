<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\TeacherAssignment;
use App\Repositories\Contracts\AcademicManagement\TeacherAssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignmentRepository extends AbstractAcademicRepository implements TeacherAssignmentRepositoryInterface
{
    protected array $filterable = ['academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status'];
    protected array $with = ['academicYear', 'schoolClass', 'section', 'subject', 'staff'];

    protected function model(): Model
    {
        return new TeacherAssignment();
    }
}
