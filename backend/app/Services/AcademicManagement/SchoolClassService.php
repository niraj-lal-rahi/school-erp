<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\SchoolClassRepositoryInterface;

class SchoolClassService extends AbstractAcademicCrudService
{
    public function __construct(SchoolClassRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
