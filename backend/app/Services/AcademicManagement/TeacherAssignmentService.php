<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\TeacherAssignmentRepositoryInterface;

class TeacherAssignmentService extends AbstractAcademicCrudService
{
    public function __construct(TeacherAssignmentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
