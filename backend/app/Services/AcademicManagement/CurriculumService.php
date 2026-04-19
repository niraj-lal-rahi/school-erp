<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\CurriculumRepositoryInterface;

class CurriculumService extends AbstractAcademicCrudService
{
    public function __construct(CurriculumRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
