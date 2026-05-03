<?php

namespace App\Support\Queue;

class JobRetryProfile
{
    public static function notifications(): array
    {
        return [30, 120, 300];
    }

    public static function payments(): array
    {
        return [15, 60, 180, 600];
    }

    public static function reports(): array
    {
        return [60, 300, 900];
    }

    public static function documents(): array
    {
        return [60, 180, 600];
    }

    public static function automation(): array
    {
        return [30, 120, 300];
    }

    public static function computation(): array
    {
        return [60, 180, 600];
    }
}
