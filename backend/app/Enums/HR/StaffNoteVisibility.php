<?php

namespace App\Enums\HR;

enum StaffNoteVisibility: string
{
    case Internal = 'internal';
    case Private = 'private';
    case AdminOnly = 'admin_only';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
