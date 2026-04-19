<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\LessonPlanRepositoryInterface;

class LessonPlanService extends AbstractAcademicCrudService
{
    public function __construct(LessonPlanRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
