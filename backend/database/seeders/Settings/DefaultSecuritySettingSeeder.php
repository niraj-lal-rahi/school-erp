<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\SecuritySetting;
use Illuminate\Database\Seeder;

class DefaultSecuritySettingSeeder extends Seeder
{
    public function run(): void
    {
        SecuritySetting::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => null],
            [
                'password_min_length' => 8,
                'password_requires_uppercase' => true,
                'password_requires_number' => true,
                'password_requires_symbol' => false,
                'session_timeout_minutes' => 120,
                'max_login_attempts' => 5,
                'lockout_minutes' => 15,
                'two_factor_enabled' => false,
            ],
        );

        School::withoutGlobalScopes()->get()->each(function (School $school): void {
            SecuritySetting::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id],
                [
                    'password_min_length' => 8,
                    'password_requires_uppercase' => true,
                    'password_requires_number' => true,
                    'password_requires_symbol' => false,
                    'session_timeout_minutes' => 120,
                    'max_login_attempts' => 5,
                    'lockout_minutes' => 15,
                    'two_factor_enabled' => false,
                ],
            );
        });
    }
}
