<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\ClassSubjectAssignmentRepositoryInterface;

class ClassSubjectAssignmentService extends AbstractAcademicCrudService
{
    public function __construct(ClassSubjectAssignmentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
