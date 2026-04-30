<?php

namespace Tests\Feature\Portal;

use App\Models\Guardian;
use App\Models\Portal\PortalNotification;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(string $email): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_student_login_context_is_resolved(): void
    {
        $headers = $this->authenticate('portal.student@greenwood.edu');

        $this->withHeaders($headers)->getJson('/api/v1/portal/context')
            ->assertOk()
            ->assertJsonPath('data.profile_mode', 'student')
            ->assertJsonPath('data.active_context.active_profile_type', 'student');
    }

    public function test_guardian_login_context_is_resolved(): void
    {
        $headers = $this->authenticate('portal.guardian@greenwood.edu');

        $this->withHeaders($headers)->getJson('/api/v1/portal/context')
            ->assertOk()
            ->assertJsonPath('data.profile_mode', 'guardian')
            ->assertJsonPath('data.active_context.active_profile_type', 'guardian');
    }

    public function test_guardian_can_see_multiple_children(): void
    {
        $headers = $this->authenticate('portal.guardian@greenwood.edu');

        $this->withHeaders($headers)->getJson('/api/v1/portal/accessible-students')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_switch_context_to_another_accessible_child(): void
    {
        $headers = $this->authenticate('portal.guardian@greenwood.edu');
        $guardian = Guardian::withoutGlobalScopes()->where('email', 'parent1@example.com')->firstOrFail();
        $secondStudent = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0002')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/portal/context/switch', [
            'active_profile_type' => 'guardian',
            'active_profile_id' => $guardian->id,
            'active_student_id' => $secondStudent->id,
        ])->assertOk()
            ->assertJsonPath('data.context.active_context.active_student_id', $secondStudent->id);
    }

    public function test_student_cannot_access_unlinked_student_data(): void
    {
        $headers = $this->authenticate('portal.student@greenwood.edu');
        $otherStudent = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0002')->firstOrFail();

        $this->withHeaders($headers)->getJson("/api/v1/portal/students/{$otherStudent->id}/overview")
            ->assertForbidden();
    }

    public function test_accessible_student_attendance_can_be_viewed(): void
    {
        $headers = $this->authenticate('portal.student@greenwood.edu');
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();

        $this->withHeaders($headers)->getJson("/api/v1/portal/students/{$student->id}/attendance")
            ->assertOk()
            ->assertJsonPath('data.summary.percentage', 92);
    }

    public function test_accessible_student_fees_can_be_viewed(): void
    {
        $headers = $this->authenticate('portal.guardian@greenwood.edu');
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();

        $response = $this->withHeaders($headers)->getJson("/api/v1/portal/students/{$student->id}/fees")
            ->assertOk();

        $this->assertGreaterThan(0, (float) $response->json('data.summary.total_due'));
    }

    public function test_accessible_student_results_can_be_viewed(): void
    {
        $headers = $this->authenticate('portal.student@greenwood.edu');
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();

        $this->withHeaders($headers)->getJson("/api/v1/portal/students/{$student->id}/results")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'latest_result',
                    'results',
                ],
            ]);
    }

    public function test_portal_notification_can_be_marked_as_read(): void
    {
        $headers = $this->authenticate('portal.student@greenwood.edu');
        $notification = PortalNotification::withoutGlobalScopes()
            ->where('title', 'Attendance Alert')
            ->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/portal/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertDatabaseHas('portal_notifications', [
            'id' => $notification->id,
            'is_read' => 1,
        ]);
    }

    public function test_admin_can_link_portal_profiles(): void
    {
        $headers = $this->authenticate('admin@greenwood.edu');
        $schoolId = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->value('school_id');
        $newUser = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $schoolId,
            'first_name' => 'Portal',
            'last_name' => 'Linked',
            'name' => 'Portal Linked',
            'email' => 'portal.linked@greenwood.edu',
            'phone' => '9666666699',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0002')->firstOrFail();
        $guardian = Guardian::withoutGlobalScopes()->where('email', 'portal.guardian.both@example.com')->firstOrFail();
        $guardianChild = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0004')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/portal/profiles/link-student', [
            'user_id' => $newUser->id,
            'profile_type' => 'student',
            'student_id' => $student->id,
            'is_default' => true,
            'status' => 'active',
        ])->assertOk();

        $this->withHeaders($headers)->postJson('/api/v1/portal/profiles/link-guardian', [
            'user_id' => $newUser->id,
            'profile_type' => 'guardian',
            'guardian_id' => $guardian->id,
            'status' => 'active',
        ])->assertOk();

        $this->assertDatabaseHas('portal_user_profiles', [
            'user_id' => $newUser->id,
            'profile_type' => 'student',
            'profile_id' => $student->id,
        ]);

        $this->assertDatabaseHas('portal_user_profiles', [
            'user_id' => $newUser->id,
            'profile_type' => 'guardian',
            'profile_id' => $guardian->id,
        ]);

        $this->assertDatabaseHas('portal_profile_access', [
            'user_id' => $newUser->id,
            'student_id' => $guardianChild->id,
            'guardian_id' => $guardian->id,
            'access_type' => 'guardian',
        ]);
    }
}
