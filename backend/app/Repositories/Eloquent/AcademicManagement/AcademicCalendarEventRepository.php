<?php

namespace App\Repositories\Eloquent\AcademicManagement;

use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Repositories\Contracts\AcademicManagement\AcademicCalendarEventRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendarEventRepository extends AbstractAcademicRepository implements AcademicCalendarEventRepositoryInterface
{
    protected array $searchable = ['title', 'event_type'];
    protected array $filterable = ['academic_year_id', 'school_class_id', 'section_id', 'status'];
    protected array $with = ['academicYear', 'schoolClass', 'section'];

    protected function model(): Model
    {
        return new AcademicCalendarEvent();
    }
}
