<?php

namespace App\Enums\AcademicManagement;

enum AcademicStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
