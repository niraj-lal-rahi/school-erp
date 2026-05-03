<?php

namespace Database\Seeders\Workflows;

use App\Models\School;
use App\Models\User;
use App\Models\Workflows\AutomationRule;
use Illuminate\Database\Seeder;

class DefaultAutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $rules = [
            [
                'name' => 'Absent Student Notification',
                'code' => 'ABSENT-STUDENT-NOTIFICATION',
                'module' => 'attendance',
                'trigger_type' => 'event',
                'trigger_event' => 'attendance.student.absent',
                'actions' => [[
                    'type' => 'in_app',
                    'recipient_type' => 'user',
                    'recipient_id' => $admin->id,
                    'subject' => 'Attendance Alert',
                    'message' => 'A student absence event requires review.',
                ]],
            ],
            [
                'name' => 'Fee Overdue Reminder',
                'code' => 'FEE-OVERDUE-REMINDER',
                'module' => 'fees',
                'trigger_type' => 'event',
                'trigger_event' => 'fee.payment.overdue',
                'actions' => [[
                    'type' => 'reminder',
                    'recipient_type' => 'user',
                    'recipient_id' => $admin->id,
                    'subject' => 'Fee Overdue',
                    'message' => 'An overdue fee requires a follow-up action.',
                ]],
            ],
            [
                'name' => 'Result Published Notification',
                'code' => 'RESULT-PUBLISHED-NOTIFICATION',
                'module' => 'exams',
                'trigger_type' => 'event',
                'trigger_event' => 'exam.result.published',
                'actions' => [[
                    'type' => 'in_app',
                    'recipient_type' => 'user',
                    'recipient_id' => $admin->id,
                    'subject' => 'Exam Results Published',
                    'message' => 'Exam results have been published successfully.',
                ]],
            ],
        ];

        foreach ($rules as $rule) {
            AutomationRule::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $rule['code'],
                ],
                [
                    'name' => $rule['name'],
                    'module' => $rule['module'],
                    'trigger_type' => $rule['trigger_type'],
                    'trigger_event' => $rule['trigger_event'],
                    'schedule_expression' => null,
                    'conditions' => null,
                    'actions' => $rule['actions'],
                    'status' => 'active',
                    'last_run_at' => null,
                    'next_run_at' => null,
                    'created_by' => $admin->id,
                ]
            );
        }
    }
}
