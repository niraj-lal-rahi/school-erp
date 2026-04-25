<?php

namespace Database\Seeders\Timetable;

use App\Models\AcademicYear;
use App\Models\AcademicManagement\Subject;
use App\Models\Attendance\AttendancePeriod;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Timetable\TimetableEntry;
use App\Models\Timetable\TimetablePublishLog;
use App\Models\Timetable\TimetableRoom;
use App\Models\Timetable\TimetableScheduleException;
use App\Models\Timetable\TimetableSubstitution;
use App\Models\Timetable\TimetableVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('email', 'admin@greenwood.edu')
            ->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'AY-2026-27')
            ->firstOrFail();

        $periods = [
            ['name' => 'Period 1', 'code' => 'P1', 'start_time' => '08:00:00', 'end_time' => '08:45:00', 'sequence' => 1, 'is_break' => false, 'break_type' => null],
            ['name' => 'Period 2', 'code' => 'P2', 'start_time' => '08:50:00', 'end_time' => '09:35:00', 'sequence' => 2, 'is_break' => false, 'break_type' => null],
            ['name' => 'Period 3', 'code' => 'P3', 'start_time' => '09:50:00', 'end_time' => '10:35:00', 'sequence' => 3, 'is_break' => false, 'break_type' => null],
            ['name' => 'Short Break', 'code' => 'BRK', 'start_time' => '10:35:00', 'end_time' => '10:50:00', 'sequence' => 4, 'is_break' => true, 'break_type' => 'short_break'],
            ['name' => 'Lunch', 'code' => 'LUNCH', 'start_time' => '12:30:00', 'end_time' => '13:00:00', 'sequence' => 5, 'is_break' => true, 'break_type' => 'lunch'],
        ];

        foreach ($periods as $period) {
            AttendancePeriod::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $period['code']],
                [
                    'name' => $period['name'],
                    'start_time' => $period['start_time'],
                    'end_time' => $period['end_time'],
                    'sequence' => $period['sequence'],
                    'is_break' => $period['is_break'],
                    'break_type' => $period['break_type'],
                    'status' => 'active',
                ]
            );
        }

        $rooms = [
            ['name' => 'Room 8A', 'code' => 'R-8A', 'room_type' => 'classroom', 'capacity' => 40, 'building' => 'Main Block', 'floor' => '1'],
            ['name' => 'Science Lab', 'code' => 'LAB-SCI', 'room_type' => 'lab', 'capacity' => 30, 'building' => 'Main Block', 'floor' => '2'],
            ['name' => 'Library Hall', 'code' => 'LIB-1', 'room_type' => 'library', 'capacity' => 60, 'building' => 'Knowledge Block', 'floor' => '1'],
        ];

        foreach ($rooms as $room) {
            TimetableRoom::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $room['code']],
                [
                    'name' => $room['name'],
                    'room_type' => $room['room_type'],
                    'capacity' => $room['capacity'],
                    'building' => $room['building'],
                    'floor' => $room['floor'],
                    'status' => 'active',
                ]
            );
        }

        $version = TimetableVersion::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'TT-2026-MAIN'],
            [
                'academic_year_id' => $academicYear->id,
                'name' => 'Main Timetable 2026-2027',
                'effective_from' => '2026-04-01',
                'effective_to' => null,
                'status' => 'draft',
                'published_at' => null,
                'created_by' => $admin->id,
            ]
        );

        TimetablePublishLog::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'timetable_version_id' => $version->id,
                'action' => 'created',
                'performed_by' => $admin->id,
            ],
            [
                'remarks' => 'Seeded timetable version created.',
            ]
        );

        $schoolClass = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'G8')
            ->first();
        $section = Section::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'A')
            ->first();
        $subject = Subject::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'MATH')
            ->first();
        $teacher = Staff::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('employee_code', 'EMP-0002')
            ->first();
        $room = TimetableRoom::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'R-8A')
            ->first();
        $periodOne = AttendancePeriod::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'P1')
            ->first();
        $periodTwo = AttendancePeriod::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'P2')
            ->first();

        if ($schoolClass && $section && $subject && $teacher && $room && $periodOne && $periodTwo) {
            $entryOne = TimetableEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'timetable_version_id' => $version->id,
                    'school_class_id' => $schoolClass->id,
                    'section_id' => $section->id,
                    'day_of_week' => 'monday',
                    'attendance_period_id' => $periodOne->id,
                ],
                [
                    'academic_year_id' => $academicYear->id,
                    'subject_id' => $subject->id,
                    'staff_id' => $teacher->id,
                    'room_id' => $room->id,
                    'entry_type' => 'class',
                    'notes' => 'Seeded mathematics slot.',
                    'status' => 'active',
                ]
            );

            TimetableEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'timetable_version_id' => $version->id,
                    'school_class_id' => $schoolClass->id,
                    'section_id' => $section->id,
                    'day_of_week' => 'monday',
                    'attendance_period_id' => $periodTwo->id,
                ],
                [
                    'academic_year_id' => $academicYear->id,
                    'subject_id' => $subject->id,
                    'staff_id' => $teacher->id,
                    'room_id' => $room->id,
                    'entry_type' => 'class',
                    'notes' => 'Seeded follow-up mathematics slot.',
                    'status' => 'active',
                ]
            );

            $backupTeacher = Staff::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('employee_code', 'EMP-0001')
                ->first();

            if ($backupTeacher) {
                TimetableSubstitution::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'timetable_entry_id' => $entryOne->id,
                        'substitution_date' => now()->next('monday')->toDateString(),
                    ],
                    [
                        'original_staff_id' => $teacher->id,
                        'substitute_staff_id' => $backupTeacher->id,
                        'reason' => 'Seeded substitution example.',
                        'status' => 'planned',
                        'approved_by' => null,
                    ]
                );
            }
        }

        if ($schoolClass && $section) {
            TimetableScheduleException::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'exception_date' => now()->addWeek()->toDateString(),
                    'title' => 'Seeded Timetable Event',
                ],
                [
                    'school_class_id' => $schoolClass->id,
                    'section_id' => $section->id,
                    'description' => 'Special seeded timetable exception.',
                    'exception_type' => 'event',
                    'affects_attendance' => false,
                ]
            );
        }
    }
}
