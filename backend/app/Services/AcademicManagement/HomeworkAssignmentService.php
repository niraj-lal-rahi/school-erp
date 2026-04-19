<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\HomeworkAssignmentRepositoryInterface;

class HomeworkAssignmentService extends AbstractAcademicCrudService
{
    public function __construct(HomeworkAssignmentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
