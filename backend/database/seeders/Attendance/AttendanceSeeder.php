<?php

namespace Database\Seeders\Attendance;

use App\Models\AcademicYear;
use App\Models\Attendance\AttendancePeriod;
use App\Models\Attendance\AttendanceHoliday;
use App\Models\Attendance\AttendanceImport;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\BiometricLog;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\HR\StaffAttendance;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $statuses = [
            ['name' => 'Present', 'code' => 'PRESENT', 'is_present' => true, 'counts_for_attendance' => true, 'color_code' => '#22c55e'],
            ['name' => 'Absent', 'code' => 'ABSENT', 'is_present' => false, 'counts_for_attendance' => false, 'color_code' => '#ef4444'],
            ['name' => 'Late', 'code' => 'LATE', 'is_present' => true, 'counts_for_attendance' => true, 'color_code' => '#f59e0b'],
            ['name' => 'Half Day', 'code' => 'HALF_DAY', 'is_present' => true, 'counts_for_attendance' => true, 'color_code' => '#3b82f6'],
            ['name' => 'Leave', 'code' => 'LEAVE', 'is_present' => false, 'counts_for_attendance' => false, 'color_code' => '#8b5cf6'],
            ['name' => 'Holiday', 'code' => 'HOLIDAY', 'is_present' => false, 'counts_for_attendance' => false, 'color_code' => '#6b7280'],
            ['name' => 'On Duty', 'code' => 'ON_DUTY', 'is_present' => true, 'counts_for_attendance' => true, 'color_code' => '#06b6d4'],
        ];

        foreach ($statuses as $status) {
            AttendanceStatusType::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $status['code']],
                [
                    'name' => $status['name'],
                    'is_present' => $status['is_present'],
                    'counts_for_attendance' => $status['counts_for_attendance'],
                    'color_code' => $status['color_code'],
                    'description' => $status['name'].' status',
                    'status' => 'active',
                ]
            );
        }

        $statusIdMap = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->pluck('id', 'code');

        $staffStatusCodeMap = [
            'present' => 'PRESENT',
            'absent' => 'ABSENT',
            'late' => 'LATE',
            'half_day' => 'HALF_DAY',
            'leave' => 'LEAVE',
            'holiday' => 'HOLIDAY',
            'on_duty' => 'ON_DUTY',
        ];

        StaffAttendance::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->get()
            ->each(function (StaffAttendance $attendance) use ($statusIdMap, $staffStatusCodeMap): void {
                $mappedCode = $staffStatusCodeMap[$attendance->attendance_status] ?? strtoupper((string) $attendance->attendance_status);
                $statusTypeId = $statusIdMap[$mappedCode] ?? null;

                if (! $statusTypeId) {
                    return;
                }

                $attendance->update([
                    'attendance_status_type_id' => $statusTypeId,
                ]);
            });

        $periods = [
            ['name' => 'Period 1', 'code' => 'P1', 'start_time' => '08:00:00', 'end_time' => '08:45:00', 'sequence' => 1],
            ['name' => 'Period 2', 'code' => 'P2', 'start_time' => '08:50:00', 'end_time' => '09:35:00', 'sequence' => 2],
            ['name' => 'Period 3', 'code' => 'P3', 'start_time' => '09:50:00', 'end_time' => '10:35:00', 'sequence' => 3],
        ];

        foreach ($periods as $period) {
            AttendancePeriod::withoutGlobalScopes()->updateOrCreate(
                ['school_id' => $school->id, 'code' => $period['code']],
                [
                    ...$period,
                    'status' => 'active',
                ]
            );
        }

        $nextHolidayYearId = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->value('id');

        if ($nextHolidayYearId) {
            AttendanceHoliday::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $nextHolidayYearId,
                    'title' => 'Seeded Staff Planning Day',
                ],
                [
                    'description' => 'Seeded staff-only holiday for testing holiday logic.',
                    'start_date' => '2026-04-30',
                    'end_date' => '2026-04-30',
                    'applies_to' => 'staff',
                    'school_class_id' => null,
                    'section_id' => null,
                    'is_recurring' => false,
                ]
            );
        }

        $academicYearId = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->value('id');
        $schoolClassId = SchoolClass::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->value('id');
        $sectionId = Section::withoutGlobalScopes()->where('school_id', $school->id)->where('school_class_id', $schoolClassId)->orderBy('id')->value('id');
        $adminId = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->value('id');

        if (! $academicYearId || ! $schoolClassId || ! $sectionId || ! $adminId) {
            return;
        }

        $session = StudentAttendanceSession::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYearId,
                'school_class_id' => $schoolClassId,
                'section_id' => $sectionId,
                'attendance_date' => now()->toDateString(),
                'session_type' => 'daily',
                'attendance_period_id' => null,
                'session_slot' => 'daily',
            ],
            [
                'subject_id' => null,
                'teacher_id' => null,
                'status' => 'draft',
                'marked_by' => $adminId,
                'submitted_at' => null,
                'locked_at' => null,
            ]
        );

        $presentId = AttendanceStatusType::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', 'PRESENT')
            ->value('id');

        $studentIds = StudentEnrollment::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $academicYearId)
            ->where('school_class_id', $schoolClassId)
            ->where('section_id', $sectionId)
            ->where('is_current', true)
            ->limit(3)
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            $session->records()->updateOrCreate(
                [
                    'student_id' => $studentId,
                ],
                [
                    'school_id' => $school->id,
                    'attendance_status_type_id' => $presentId,
                    'remarks' => 'Seeded attendance record.',
                    'marked_by' => $adminId,
                ]
            );
        }

        if ($studentIds->isNotEmpty()) {
            AttendanceSummary::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'user_type' => 'student',
                    'user_id' => $studentIds->first(),
                    'academic_year_id' => $academicYearId,
                ],
                [
                    'total_days' => 1,
                    'present_days' => 1,
                    'absent_days' => 0,
                    'leave_days' => 0,
                    'late_days' => 0,
                    'percentage' => 100,
                    'last_updated_at' => now(),
                ]
            );
        }

        AttendanceImport::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'import_type' => 'staff',
                'import_date' => now()->toDateString(),
                'uploaded_by' => $adminId,
            ],
            [
                'file_path' => null,
                'status' => 'pending',
                'total_records' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'logs' => ['payload_rows' => [], 'messages' => []],
            ]
        );

        if ($studentIds->isNotEmpty()) {
            BiometricLog::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'user_type' => 'student',
                    'user_id' => $studentIds->first(),
                    'log_datetime' => now()->toDateTimeString(),
                    'log_type' => 'check_in',
                ],
                [
                    'device_id' => 'DEVICE-1',
                    'raw_data' => ['seeded' => true],
                    'processed' => false,
                ]
            );
        }
    }
}
