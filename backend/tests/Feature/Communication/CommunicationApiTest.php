<?php

namespace Tests\Feature\Communication;

use App\Models\Communication\CommunicationGroup;
use App\Models\Communication\NotificationLog;
use App\Models\Communication\NotificationPreference;
use App\Models\Communication\ScheduledMessage;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_create_announcement(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->postJson('/api/v1/communication/announcements', [
            'title' => 'Fee Counter Timing',
            'content' => 'The fee counter will stay open until 4 PM.',
            'announcement_type' => 'fee',
            'audience_type' => 'all',
            'priority' => 'normal',
            'status' => 'draft',
        ])->assertCreated();

        $this->assertDatabaseHas('announcements', [
            'id' => $response->json('data.id'),
            'title' => 'Fee Counter Timing',
            'announcement_type' => 'fee',
        ]);
    }

    public function test_publishing_announcement_creates_notification_logs(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->firstOrFail();

        $announcementResponse = $this->withHeaders($headers)->postJson('/api/v1/communication/announcements', [
            'title' => 'Attendance Update',
            'content' => 'Please check the attendance dashboard regularly.',
            'announcement_type' => 'attendance',
            'audience_type' => 'individual',
            'priority' => 'high',
            'status' => 'draft',
            'recipients' => [[
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
            ]],
        ])->assertCreated();

        $announcementId = $announcementResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/communication/announcements/{$announcementId}/publish", [
            'channels' => ['in_app'],
        ])->assertOk();

        $this->assertDatabaseHas('notification_logs', [
            'notifiable_type' => 'student',
            'notifiable_id' => $student->id,
            'channel' => 'in_app',
            'subject' => 'Attendance Update',
        ]);
    }

    public function test_class_audience_resolves_student_recipients(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->firstOrFail();
        $enrollment = $student->enrollments()->where('is_current', true)->firstOrFail();

        $response = $this->withHeaders($headers)->postJson('/api/v1/communication/announcements', [
            'title' => 'Class Activity',
            'content' => 'Class activity starts tomorrow.',
            'announcement_type' => 'academic',
            'audience_type' => 'class',
            'class_id' => $enrollment->school_class_id,
            'priority' => 'normal',
            'status' => 'draft',
        ])->assertCreated();

        $announcementId = $response->json('data.id');

        $this->withHeaders($headers)->getJson("/api/v1/communication/announcements/{$announcementId}/recipients")
            ->assertOk()
            ->assertJsonFragment([
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
            ]);
    }

    public function test_authorized_user_can_send_direct_message(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/communication/messages', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'subject' => 'Homework Reminder',
            'body' => 'Please submit the worksheet by tomorrow.',
            'message_type' => 'direct',
            'priority' => 'normal',
            'channels' => ['in_app'],
        ])->assertCreated()->assertJsonPath('data.recipient_id', $student->id);

        $this->assertDatabaseHas('communication_messages', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'subject' => 'Homework Reminder',
        ]);
    }

    public function test_message_sending_creates_notification_log(): void
    {
        $headers = $this->authenticate();
        $guardian = Guardian::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/communication/messages', [
            'recipient_type' => 'guardian',
            'recipient_id' => $guardian->id,
            'subject' => 'PTM Reminder',
            'body' => 'Parent teacher meeting is scheduled for Friday.',
            'message_type' => 'direct',
            'priority' => 'normal',
            'channels' => ['in_app'],
        ])->assertCreated();

        $this->assertDatabaseHas('notification_logs', [
            'notifiable_type' => 'guardian',
            'notifiable_id' => $guardian->id,
            'channel' => 'in_app',
            'subject' => 'PTM Reminder',
        ]);
    }

    public function test_due_scheduled_message_can_be_processed(): void
    {
        $headers = $this->authenticate();
        $scheduledMessage = ScheduledMessage::withoutGlobalScopes()->where('status', 'pending')->firstOrFail();
        $scheduledMessage->update([
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->withHeaders($headers)->postJson('/api/v1/communication/scheduled-messages/process-due')
            ->assertOk();

        $this->assertDatabaseHas('scheduled_messages', [
            'id' => $scheduledMessage->id,
            'status' => 'sent',
        ]);
    }

    public function test_notification_preferences_are_respected_for_in_app_delivery(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->firstOrFail();

        NotificationPreference::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $student->school_id,
                'user_type' => 'student',
                'user_id' => $student->id,
            ],
            [
                'email_enabled' => true,
                'sms_enabled' => true,
                'push_enabled' => true,
                'in_app_enabled' => false,
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
            ]
        );

        $this->withHeaders($headers)->postJson('/api/v1/communication/messages', [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'subject' => 'Preference Check',
            'body' => 'This should not create an in-app notification log.',
            'message_type' => 'direct',
            'priority' => 'normal',
            'channels' => ['in_app'],
        ])->assertCreated();

        $this->assertDatabaseMissing('notification_logs', [
            'notifiable_type' => 'student',
            'notifiable_id' => $student->id,
            'channel' => 'in_app',
            'subject' => 'Preference Check',
        ]);
    }

    public function test_group_members_can_be_added_and_removed(): void
    {
        $headers = $this->authenticate();
        $admin = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $staff = Staff::withoutGlobalScopes()->orderByDesc('id')->firstOrFail();
        $group = CommunicationGroup::withoutGlobalScopes()->create([
            'school_id' => $admin->school_id,
            'name' => 'Test Group',
            'code' => 'TEST-GROUP-001',
            'group_type' => 'custom',
            'status' => 'active',
        ]);

        $addResponse = $this->withHeaders($headers)->postJson("/api/v1/communication/groups/{$group->id}/members", [
            'member_type' => 'staff',
            'member_id' => $staff->id,
        ])->assertCreated();

        $memberId = $addResponse->json('data.id');

        $this->withHeaders($headers)->deleteJson("/api/v1/communication/groups/{$group->id}/members/{$memberId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('communication_group_members', [
            'id' => $memberId,
        ]);
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $headers = $this->authenticate();
        $notification = NotificationLog::withoutGlobalScopes()->where('channel', 'in_app')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/communication/notifications/{$notification->id}/mark-read")
            ->assertOk()
            ->assertJsonPath('data.status', 'read');

        $this->assertDatabaseHas('notification_logs', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }
}
