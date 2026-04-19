<?php

namespace App\Services\AcademicManagement;

use App\Repositories\Contracts\AcademicManagement\AcademicCalendarEventRepositoryInterface;

class AcademicCalendarEventService extends AbstractAcademicCrudService
{
    public function __construct(AcademicCalendarEventRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
