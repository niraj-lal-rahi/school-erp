<?php

namespace App\Support\Pagination;

class PaginationDefaults
{
    public static function resolvePerPage(?int $requested, int $default = 15, int $max = 100): int
    {
        $perPage = $requested ?? $default;

        if ($perPage < 1) {
            return $default;
        }

        return min($perPage, $max);
    }

    public static function resolveCursorPerPage(?int $requested, int $default = 25, int $max = 200): int
    {
        return self::resolvePerPage($requested, $default, $max);
    }
}
