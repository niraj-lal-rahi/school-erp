<?php

namespace Database\Seeders\Settings;

use App\Models\School;
use App\Models\Settings\Setting;
use App\Models\Settings\SettingGroup;
use Illuminate\Database\Seeder;

class DefaultSettingSeeder extends Seeder
{
    public function run(): void
    {
        $globalGroups = SettingGroup::withoutGlobalScopes()
            ->whereNull('school_id')
            ->get()
            ->keyBy('code');

        $globalSettings = [
            [
                'group_code' => 'general',
                'key' => 'app_name',
                'value' => 'School ERP',
                'value_type' => 'string',
                'scope' => 'global',
                'is_public' => true,
                'description' => 'Application display name.',
            ],
            [
                'group_code' => 'academic',
                'key' => 'academic.default_attendance_mode',
                'value' => 'daily',
                'value_type' => 'string',
                'scope' => 'global',
                'is_public' => false,
                'description' => 'Default attendance mode.',
            ],
            [
                'group_code' => 'finance',
                'key' => 'finance.receipt_prefix',
                'value' => 'RCT',
                'value_type' => 'string',
                'scope' => 'global',
                'is_public' => false,
                'description' => 'Default receipt prefix.',
            ],
            [
                'group_code' => 'notifications',
                'key' => 'notifications.default_channels',
                'value' => json_encode(['email', 'in_app']),
                'value_type' => 'json',
                'scope' => 'global',
                'is_public' => false,
                'description' => 'Default notification channels.',
            ],
            [
                'group_code' => 'branding',
                'key' => 'branding.show_powered_by',
                'value' => '1',
                'value_type' => 'boolean',
                'scope' => 'global',
                'is_public' => true,
                'description' => 'Whether to show powered-by text.',
            ],
            [
                'group_code' => 'integrations',
                'key' => 'integrations.support_email',
                'value' => 'support@schoolerp.local',
                'value_type' => 'string',
                'scope' => 'global',
                'is_public' => true,
                'description' => 'Public support email.',
            ],
        ];

        foreach ($globalSettings as $setting) {
            Setting::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => null,
                    'scope' => 'global',
                    'key' => $setting['key'],
                ],
                [
                    'group_id' => $globalGroups[$setting['group_code']]->id ?? null,
                    'value' => $setting['value'],
                    'value_type' => $setting['value_type'],
                    'is_sensitive' => false,
                    'is_public' => $setting['is_public'],
                    'description' => $setting['description'],
                ],
            );
        }

        School::withoutGlobalScopes()->get()->each(function (School $school): void {
            $groups = SettingGroup::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->get()
                ->keyBy('code');

            $tenantSettings = [
                [
                    'group_code' => 'general',
                    'key' => 'school.portal_welcome_message',
                    'value' => sprintf('Welcome to %s', $school->name),
                    'value_type' => 'string',
                    'is_public' => true,
                    'description' => 'Portal welcome message.',
                ],
                [
                    'group_code' => 'academic',
                    'key' => 'academic.grade_scale',
                    'value' => json_encode(['A', 'B', 'C', 'D']),
                    'value_type' => 'json',
                    'is_public' => false,
                    'description' => 'Default grade scale.',
                ],
                [
                    'group_code' => 'finance',
                    'key' => 'finance.late_fee_enabled',
                    'value' => '1',
                    'value_type' => 'boolean',
                    'is_public' => false,
                    'description' => 'Whether late fee is enabled.',
                ],
                [
                    'group_code' => 'notifications',
                    'key' => 'notifications.sender_name',
                    'value' => $school->name,
                    'value_type' => 'string',
                    'is_public' => false,
                    'description' => 'Sender name for outbound communication.',
                ],
            ];

            foreach ($tenantSettings as $setting) {
                Setting::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'scope' => 'tenant',
                        'key' => $setting['key'],
                    ],
                    [
                        'group_id' => $groups[$setting['group_code']]->id ?? null,
                        'value' => $setting['value'],
                        'value_type' => $setting['value_type'],
                        'is_sensitive' => false,
                        'is_public' => $setting['is_public'],
                        'description' => $setting['description'],
                    ],
                );
            }
        });
    }
}
