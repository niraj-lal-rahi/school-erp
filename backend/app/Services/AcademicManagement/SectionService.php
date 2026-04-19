<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\SectionRepositoryInterface;

class SectionService extends AbstractAcademicCrudService
{
    public function __construct(SectionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
