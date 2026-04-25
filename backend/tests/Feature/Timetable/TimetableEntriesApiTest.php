<?php

namespace Tests\Feature\Timetable;

use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendancePeriod;
use App\Models\HR\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Timetable\TimetableRoom;
use App\Models\Timetable\TimetableVersion;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableEntriesApiTest extends TestCase
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

    protected function payloadOverrides(array $overrides = []): array
    {
        $academicYear = AcademicYear::withoutGlobalScopes()->where('code', 'AY-2026-27')->firstOrFail();
        $version = TimetableVersion::withoutGlobalScopes()->where('code', 'TT-2026-MAIN')->firstOrFail();
        $schoolClass = SchoolClass::withoutGlobalScopes()->where('code', 'G8')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('code', 'A')->firstOrFail();
        $subject = Subject::withoutGlobalScopes()->where('code', 'MATH')->firstOrFail();
        $teacher = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();
        $room = TimetableRoom::withoutGlobalScopes()->where('code', 'R-8A')->firstOrFail();
        $period = AttendancePeriod::withoutGlobalScopes()->where('code', 'P3')->firstOrFail();

        return [
            'timetable_version_id' => $version->id,
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'day_of_week' => 'tuesday',
            'attendance_period_id' => $period->id,
            'subject_id' => $subject->id,
            'staff_id' => $teacher->id,
            'room_id' => $room->id,
            'entry_type' => 'class',
            'notes' => 'Created in timetable entry test.',
            'status' => 'active',
            ...$overrides,
        ];
    }

    public function test_can_create_timetable_entry(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/entries', $this->payloadOverrides())
            ->assertCreated()
            ->assertJsonPath('data.day_of_week', 'tuesday')
            ->assertJsonPath('data.period.code', 'P3');
    }

    public function test_conflict_check_returns_teacher_and_room_conflicts(): void
    {
        $headers = $this->authenticate();
        $teacher = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();
        $room = TimetableRoom::withoutGlobalScopes()->where('code', 'R-8A')->firstOrFail();
        $period = AttendancePeriod::withoutGlobalScopes()->where('code', 'P1')->firstOrFail();

        $response = $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/entries/check-conflicts', $this->payloadOverrides([
                'day_of_week' => 'monday',
                'attendance_period_id' => $period->id,
                'staff_id' => $teacher->id,
                'room_id' => $room->id,
            ]))
            ->assertOk()
            ->json('data');

        $this->assertTrue($response['has_conflicts']);
        $types = collect($response['conflicts'])->pluck('type')->all();

        $this->assertContains('class_slot', $types);
        $this->assertContains('teacher', $types);
        $this->assertContains('room', $types);
    }

    public function test_bulk_create_creates_multiple_entries_in_single_transaction(): void
    {
        $headers = $this->authenticate();
        $periodOne = AttendancePeriod::withoutGlobalScopes()->where('code', 'P2')->firstOrFail();
        $periodTwo = AttendancePeriod::withoutGlobalScopes()->where('code', 'P3')->firstOrFail();

        $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/entries/bulk-create', [
                'entries' => [
                    $this->payloadOverrides([
                        'day_of_week' => 'wednesday',
                        'attendance_period_id' => $periodOne->id,
                    ]),
                    $this->payloadOverrides([
                        'day_of_week' => 'thursday',
                        'attendance_period_id' => $periodTwo->id,
                    ]),
                ],
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'data');
    }

    public function test_weekly_views_return_seeded_entries(): void
    {
        $headers = $this->authenticate();
        $schoolClass = SchoolClass::withoutGlobalScopes()->where('code', 'G8')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('code', 'A')->firstOrFail();
        $teacher = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();

        $this->withHeaders($headers)
            ->getJson("/api/v1/timetable/classes/{$schoolClass->id}/sections/{$section->id}/weekly")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withHeaders($headers)
            ->getJson("/api/v1/timetable/staff/{$teacher->id}/weekly")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
