<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\SettingGroup;
use Illuminate\Database\Seeder;

class SettingGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'General', 'code' => 'general', 'description' => 'General application settings.', 'sort_order' => 10],
            ['name' => 'Academic', 'code' => 'academic', 'description' => 'Academic defaults and academic year settings.', 'sort_order' => 20],
            ['name' => 'Finance', 'code' => 'finance', 'description' => 'Finance and billing defaults.', 'sort_order' => 30],
            ['name' => 'Notifications', 'code' => 'notifications', 'description' => 'Communication and notification defaults.', 'sort_order' => 40],
            ['name' => 'Branding', 'code' => 'branding', 'description' => 'Branding and public display settings.', 'sort_order' => 50],
            ['name' => 'Security', 'code' => 'security', 'description' => 'Security and password controls.', 'sort_order' => 60],
            ['name' => 'Integrations', 'code' => 'integrations', 'description' => 'Third-party integration settings.', 'sort_order' => 70],
        ];

        foreach ($groups as $group) {
            SettingGroup::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => null,
                    'code' => $group['code'],
                ],
                [
                    ...$group,
                    'status' => 'active',
                ],
            );
        }

        School::withoutGlobalScopes()->get()->each(function (School $school) use ($groups): void {
            foreach ($groups as $group) {
                SettingGroup::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'code' => $group['code'],
                    ],
                    [
                        ...$group,
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
