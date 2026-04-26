<?php

namespace Database\Seeders\Communication;

use App\Models\AcademicYear;
use App\Models\Communication\Announcement;
use App\Models\Communication\CommunicationConversation;
use App\Models\Communication\CommunicationMessage;
use App\Models\Communication\NotificationLog;
use App\Models\Communication\NotificationPreference;
use App\Models\Communication\ScheduledMessage;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunicationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $school->id)->where('is_current', true)->first();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();
        $section = $class
            ? Section::withoutGlobalScopes()->where('school_id', $school->id)->where('school_class_id', $class->id)->orderBy('id')->first()
            : null;
        $user = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->first();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();
        $guardian = Guardian::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();
        $staff = Staff::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->first();

        $announcement = Announcement::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'title' => 'Welcome Back to School'],
            [
                'academic_year_id' => $academicYear?->id,
                'content' => 'We are excited to welcome everyone for the new academic session.',
                'announcement_type' => 'general',
                'audience_type' => 'all',
                'class_id' => null,
                'section_id' => null,
                'publish_at' => now()->subDay(),
                'expires_at' => now()->addDays(10),
                'priority' => 'normal',
                'status' => 'published',
                'created_by' => $user?->id,
                'published_by' => $user?->id,
                'published_at' => now()->subDay(),
            ]
        );

        if ($student) {
            $announcement->recipients()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'recipient_type' => 'student',
                    'recipient_id' => $student->id,
                ],
                ['read_at' => null, 'acknowledged_at' => null]
            );
        }

        if ($guardian) {
            NotificationPreference::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'user_type' => 'guardian',
                    'user_id' => $guardian->id,
                ],
                [
                    'email_enabled' => true,
                    'sms_enabled' => true,
                    'push_enabled' => false,
                    'in_app_enabled' => true,
                    'quiet_hours_start' => null,
                    'quiet_hours_end' => null,
                ]
            );
        }

        $conversation = CommunicationConversation::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'conversation_type' => 'parent_teacher',
                'title' => 'Parent Teacher Coordination',
            ],
            [
                'created_by' => $user?->id,
                'status' => 'active',
            ]
        );

        if ($guardian) {
            $conversation->participants()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'participant_type' => 'guardian',
                    'participant_id' => $guardian->id,
                ],
                ['joined_at' => now(), 'is_muted' => false]
            );
        }

        if ($staff) {
            $conversation->participants()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'participant_type' => 'staff',
                    'participant_id' => $staff->id,
                ],
                ['joined_at' => now(), 'is_muted' => false]
            );
        }

        if ($guardian && $user) {
            CommunicationMessage::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'conversation_id' => $conversation->id,
                    'sender_type' => 'user',
                    'sender_id' => $user->id,
                    'recipient_type' => 'guardian',
                    'recipient_id' => $guardian->id,
                ],
                [
                    'subject' => 'Welcome Message',
                    'body' => 'Please reach out here for academic updates and concerns.',
                    'message_type' => 'direct',
                    'priority' => 'normal',
                    'status' => 'sent',
                    'sent_at' => now()->subHours(2),
                    'read_at' => null,
                ]
            );
        }

        if ($guardian) {
            NotificationLog::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'notifiable_type' => 'guardian',
                    'notifiable_id' => $guardian->id,
                    'channel' => 'in_app',
                    'subject' => 'Welcome Message',
                ],
                [
                    'template_id' => null,
                    'message' => 'Please reach out here for academic updates and concerns.',
                    'provider' => 'in_app',
                    'provider_message_id' => null,
                    'status' => 'sent',
                    'error_message' => null,
                    'sent_at' => now()->subHours(2),
                    'delivered_at' => now()->subHours(2),
                    'read_at' => null,
                ]
            );
        }

        ScheduledMessage::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Exam Notice Follow-up',
            ],
            [
                'template_id' => null,
                'message' => 'Exam timetable will be shared soon.',
                'audience_type' => 'class',
                'class_id' => $class?->id,
                'section_id' => $section?->id,
                'channel' => 'in_app',
                'scheduled_at' => now()->addDay(),
                'status' => 'pending',
                'created_by' => $user?->id,
                'processed_at' => null,
            ]
        );
    }
}
