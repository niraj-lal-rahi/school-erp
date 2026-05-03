<?php

namespace Database\Seeders\Workflows;

use App\Models\Communication\MessageTemplate;
use App\Models\School;
use App\Models\Workflows\ReminderRule;
use Illuminate\Database\Seeder;

class DefaultReminderRuleSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $template = MessageTemplate::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->first();

        $rules = [
            [
                'name' => 'Fee Overdue Daily Reminder',
                'code' => 'FEE-OVERDUE-DAILY',
                'module' => 'fees',
                'reminder_type' => 'after_due',
                'offset_days' => 1,
                'frequency' => 'daily',
                'channel' => 'in_app',
            ],
            [
                'name' => 'Attendance Concern Reminder',
                'code' => 'ATTENDANCE-CONSERN-ONE-TIME',
                'module' => 'attendance',
                'reminder_type' => 'one_time',
                'offset_days' => null,
                'frequency' => 'once',
                'channel' => 'in_app',
            ],
        ];

        foreach ($rules as $rule) {
            ReminderRule::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $rule['code'],
                ],
                [
                    'name' => $rule['name'],
                    'module' => $rule['module'],
                    'reminder_type' => $rule['reminder_type'],
                    'offset_days' => $rule['offset_days'],
                    'frequency' => $rule['frequency'],
                    'channel' => $rule['channel'],
                    'template_id' => $template?->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
