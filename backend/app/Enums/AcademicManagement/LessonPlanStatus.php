<?php

namespace App\Enums\AcademicManagement;

enum LessonPlanStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
