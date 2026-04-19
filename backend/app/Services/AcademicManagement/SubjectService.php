<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\SubjectRepositoryInterface;

class SubjectService extends AbstractAcademicCrudService
{
    public function __construct(SubjectRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
