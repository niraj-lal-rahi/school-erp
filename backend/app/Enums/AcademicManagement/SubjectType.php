<?php

namespace App\Enums\AcademicManagement;

enum SubjectType: string
{
    case Mandatory = 'mandatory';
    case Elective = 'elective';
    case Practical = 'practical';
}
