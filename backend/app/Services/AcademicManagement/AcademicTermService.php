<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\AcademicTermRepositoryInterface;

class AcademicTermService extends AbstractAcademicCrudService
{
    public function __construct(AcademicTermRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
