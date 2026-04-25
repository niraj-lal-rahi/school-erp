<?php

namespace Tests\Feature\Timetable;

use App\Models\AcademicYear;
use App\Models\Attendance\AttendancePeriod;
use App\Models\HR\Department;
use App\Models\HR\Designation;
use App\Models\HR\Staff;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Timetable\TimetableEntry;
use App\Models\Timetable\TimetableRoom;
use App\Models\Timetable\TimetableVersion;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableSubstitutionsExceptionsApiTest extends TestCase
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

    public function test_can_create_and_approve_timetable_substitution(): void
    {
        $headers = $this->authenticate();
        $entry = TimetableEntry::withoutGlobalScopes()->firstOrFail();
        $schoolId = $entry->school_id;
        $department = Department::withoutGlobalScopes()->where('school_id', $schoolId)->where('code', 'TEACH')->firstOrFail();
        $designation = Designation::withoutGlobalScopes()->where('school_id', $schoolId)->where('code', 'TEACHER')->firstOrFail();

        $substitute = Staff::withoutGlobalScopes()->create([
            'school_id' => $schoolId,
            'employee_code' => 'EMP-0003',
            'user_id' => null,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'first_name' => 'Rahul',
            'middle_name' => null,
            'last_name' => 'Sen',
            'full_name' => 'Rahul Sen',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'email' => 'rahul.sen@greenwood.edu',
            'phone' => '9000000099',
            'alternate_phone' => null,
            'photo_path' => null,
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'joining_date' => now()->subMonths(6)->toDateString(),
            'leaving_date' => null,
            'current_status' => 'active',
            'qualification_summary' => 'B.Ed.',
            'experience_years' => 4,
            'address_line1' => null,
            'address_line2' => null,
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'postal_code' => '560001',
            'notes' => null,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $date = now()->next('monday')->addWeek()->toDateString();

        $substitutionId = $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/substitutions', [
                'timetable_entry_id' => $entry->id,
                'original_staff_id' => $entry->staff_id,
                'substitute_staff_id' => $substitute->id,
                'substitution_date' => $date,
                'reason' => 'Teacher unavailable for training.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'planned')
            ->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/timetable/substitutions/{$substitutionId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_approve_rejects_substitute_teacher_conflict(): void
    {
        $headers = $this->authenticate();
        $entry = TimetableEntry::withoutGlobalScopes()->firstOrFail();
        $room = TimetableRoom::withoutGlobalScopes()->where('school_id', $entry->school_id)->where('code', 'LAB-SCI')->firstOrFail();
        $teacher = Staff::withoutGlobalScopes()->where('school_id', $entry->school_id)->where('employee_code', 'EMP-0002')->firstOrFail();
        $substitute = Staff::withoutGlobalScopes()->where('school_id', $entry->school_id)->where('employee_code', 'EMP-0001')->firstOrFail();
        $version = TimetableVersion::withoutGlobalScopes()->findOrFail($entry->timetable_version_id);
        $academicYear = AcademicYear::withoutGlobalScopes()->findOrFail($entry->academic_year_id);
        $schoolClass = SchoolClass::withoutGlobalScopes()->findOrFail($entry->school_class_id);
        $section = Section::withoutGlobalScopes()->findOrFail($entry->section_id);
        $period = AttendancePeriod::withoutGlobalScopes()->findOrFail($entry->attendance_period_id);

        $conflictSection = Section::withoutGlobalScopes()->create([
            'school_id' => $entry->school_id,
            'school_class_id' => $schoolClass->id,
            'uuid' => (string) Str::uuid(),
            'name' => 'B',
            'code' => 'B',
            'capacity' => 35,
            'class_teacher_id' => null,
            'status' => 'active',
        ]);

        $department = Department::withoutGlobalScopes()->where('school_id', $entry->school_id)->where('code', 'TEACH')->firstOrFail();
        $designation = Designation::withoutGlobalScopes()->where('school_id', $entry->school_id)->where('code', 'TEACHER')->firstOrFail();
        $original = Staff::withoutGlobalScopes()->create([
            'school_id' => $entry->school_id,
            'employee_code' => 'EMP-0004',
            'user_id' => null,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'first_name' => 'Isha',
            'middle_name' => null,
            'last_name' => 'Kapoor',
            'full_name' => 'Isha Kapoor',
            'gender' => 'female',
            'date_of_birth' => '1992-02-02',
            'email' => 'isha.kapoor@greenwood.edu',
            'phone' => '9000000088',
            'alternate_phone' => null,
            'photo_path' => null,
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'joining_date' => now()->subMonths(6)->toDateString(),
            'leaving_date' => null,
            'current_status' => 'active',
            'qualification_summary' => 'M.A.',
            'experience_years' => 5,
            'address_line1' => null,
            'address_line2' => null,
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'postal_code' => '560001',
            'notes' => null,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $entry->update(['staff_id' => $original->id]);

        $date = now()->next($entry->day_of_week)->addWeek()->toDateString();

        $substitutionId = $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/substitutions', [
                'timetable_entry_id' => $entry->id,
                'original_staff_id' => $original->id,
                'substitute_staff_id' => $substitute->id,
                'substitution_date' => $date,
                'reason' => 'Try conflicting substitute.',
            ])
            ->assertCreated()
            ->json('data.id');

        TimetableEntry::withoutGlobalScopes()->create([
            'school_id' => $entry->school_id,
            'timetable_version_id' => $version->id,
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $schoolClass->id,
            'section_id' => $conflictSection->id,
            'day_of_week' => $entry->day_of_week,
            'attendance_period_id' => $period->id,
            'subject_id' => $entry->subject_id,
            'staff_id' => $substitute->id,
            'room_id' => $room->id,
            'entry_type' => 'activity',
            'notes' => 'Conflict seed.',
            'status' => 'active',
        ]);

        $this->withHeaders($headers)
            ->postJson("/api/v1/timetable/substitutions/{$substitutionId}/approve")
            ->assertStatus(422);
    }

    public function test_can_manage_schedule_exception_records(): void
    {
        $headers = $this->authenticate();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('code', 'AY-2026-27')->firstOrFail();
        $schoolClass = SchoolClass::withoutGlobalScopes()->where('code', 'G8')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('code', 'A')->firstOrFail();

        $exceptionId = $this->withHeaders($headers)
            ->postJson('/api/v1/timetable/exceptions', [
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'exception_date' => now()->addWeek()->toDateString(),
                'title' => 'Founders Day Rehearsal',
                'description' => 'Special activity timetable for rehearsal.',
                'exception_type' => 'special_schedule',
                'affects_attendance' => false,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->withHeaders($headers)
            ->getJson('/api/v1/timetable/exceptions')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Founders Day Rehearsal']);

        $this->withHeaders($headers)
            ->putJson("/api/v1/timetable/exceptions/{$exceptionId}", [
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'exception_date' => now()->addWeek()->toDateString(),
                'title' => 'Founders Day Rehearsal Updated',
                'description' => 'Updated special schedule details.',
                'exception_type' => 'special_schedule',
                'affects_attendance' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.affects_attendance', true);
    }
}
