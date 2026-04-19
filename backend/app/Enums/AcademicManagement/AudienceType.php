<?php

namespace App\Enums\AcademicManagement;

enum AudienceType: string
{
    case All = 'all';
    case Staff = 'staff';
    case Students = 'students';
    case Parents = 'parents';
    case Class = 'class';
    case Section = 'section';
}
