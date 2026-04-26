<?php

namespace Database\Seeders\Communication;

use App\Models\Communication\MessageTemplate;
use App\Models\School;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $templates = [
            [
                'name' => 'Attendance Absent Alert',
                'code' => 'ATTENDANCE-ABSENT',
                'template_type' => 'sms',
                'subject' => null,
                'body' => 'Dear parent, {{student_name}} was marked absent on {{attendance_date}}.',
                'variables' => ['student_name', 'attendance_date'],
                'status' => 'active',
            ],
            [
                'name' => 'Fee Reminder',
                'code' => 'FEE-REMINDER',
                'template_type' => 'email',
                'subject' => 'Fee Reminder for {{student_name}}',
                'body' => 'Dear {{recipient_name}}, your fee invoice {{invoice_no}} is due on {{due_date}}.',
                'variables' => ['student_name', 'recipient_name', 'invoice_no', 'due_date'],
                'status' => 'active',
            ],
            [
                'name' => 'Exam Notice',
                'code' => 'EXAM-NOTICE',
                'template_type' => 'in_app',
                'subject' => 'Upcoming Examination Notice',
                'body' => 'Exam schedule for {{class_name}} has been published.',
                'variables' => ['class_name'],
                'status' => 'active',
            ],
            [
                'name' => 'General Announcement',
                'code' => 'GENERAL-ANNOUNCEMENT',
                'template_type' => 'in_app',
                'subject' => 'School Announcement',
                'body' => '{{announcement_body}}',
                'variables' => ['announcement_body'],
                'status' => 'active',
            ],
            [
                'name' => 'Emergency Alert',
                'code' => 'EMERGENCY-ALERT',
                'template_type' => 'push',
                'subject' => 'Emergency Alert',
                'body' => '{{alert_message}}',
                'variables' => ['alert_message'],
                'status' => 'active',
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $template['code']],
                $template
            );
        }
    }
}
