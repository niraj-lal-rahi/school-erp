<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\BiometricLogData;
use App\Events\Attendance\BiometricLogsQueued;
use App\Jobs\Attendance\ProcessBiometricLogsJob;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\BiometricLog;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\Attendance\BiometricLogRepositoryInterface;
use App\Services\HR\StaffAttendanceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BiometricLogService
{
    public function __construct(
        protected BiometricLogRepositoryInterface $biometricLogs,
        protected StudentAttendanceSessionService $studentSessions,
        protected StaffAttendanceService $staffAttendance,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->biometricLogs->all($filters);
    }

    public function create(BiometricLogData $data): BiometricLog
    {
        return DB::transaction(fn (): BiometricLog => $this->biometricLogs->create($data));
    }

    public function queueProcess(int $schoolId, array $filters = []): void
    {
        event(new BiometricLogsQueued($schoolId, $filters));

        if (app()->runningUnitTests()) {
            ProcessBiometricLogsJob::dispatchSync($schoolId, $filters);
        } else {
            ProcessBiometricLogsJob::dispatch($schoolId, $filters);
        }
    }

    public function processPending(int $schoolId, array $filters = []): void
    {
        $logs = $this->biometricLogs->pending($filters)->where('school_id', $schoolId);

        foreach ($logs as $log) {
            if ($log->user_type === 'staff') {
                $this->processStaffLog($log);
            } else {
                $this->processStudentLog($log);
            }

            $this->biometricLogs->update($log, BiometricLogData::fromArray([
                ...$log->toArray(),
                'processed' => true,
            ]));
        }
    }

    protected function processStaffLog(BiometricLog $log): void
    {
        $staff = Staff::query()->findOrFail($log->user_id);
        $presentStatusId = (int) AttendanceStatusType::query()
            ->where('school_id', $staff->school_id)
            ->where('code', 'PRESENT')
            ->value('id');

        $existing = \App\Models\HR\StaffAttendance::query()
            ->where('staff_id', $staff->id)
            ->whereDate('attendance_date', $log->log_datetime?->toDateString())
            ->first();

        if ($existing) {
            $this->staffAttendance->update($existing, \App\DataTransferObjects\HR\StaffAttendanceData::fromArray([
                'attendance_date' => $log->log_datetime?->toDateString(),
                'attendance_status_type_id' => $presentStatusId,
                'check_in_time' => $log->log_type === 'check_in' ? $log->log_datetime?->format('H:i') : $existing->check_in_time,
                'check_out_time' => $log->log_type === 'check_out' ? $log->log_datetime?->format('H:i') : $existing->check_out_time,
                'source' => 'biometric',
                'remarks' => 'Processed from biometric log.',
            ]), $existing->marked_by ?? 1);

            return;
        }

        $this->staffAttendance->create($staff, \App\DataTransferObjects\HR\StaffAttendanceData::fromArray([
            'attendance_date' => $log->log_datetime?->toDateString(),
            'attendance_status_type_id' => $presentStatusId,
            'check_in_time' => $log->log_type === 'check_in' ? $log->log_datetime?->format('H:i') : null,
            'check_out_time' => $log->log_type === 'check_out' ? $log->log_datetime?->format('H:i') : null,
            'source' => 'biometric',
            'remarks' => 'Processed from biometric log.',
        ]), 1);
    }

    protected function processStudentLog(BiometricLog $log): void
    {
        $student = Student::query()->findOrFail($log->user_id);
        $attendanceDate = $log->log_datetime?->toDateString();
        $academicYearId = AcademicYear::query()
            ->whereDate('start_date', '<=', $attendanceDate)
            ->whereDate('end_date', '>=', $attendanceDate)
            ->value('id');
        $enrollment = StudentEnrollment::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('is_current', true)
            ->firstOrFail();
        $presentStatusId = (int) AttendanceStatusType::query()
            ->where('school_id', $student->school_id)
            ->where('code', 'PRESENT')
            ->value('id');

        $session = StudentAttendanceSession::query()
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->where('school_class_id', $enrollment->school_class_id)
            ->where('section_id', $enrollment->section_id)
            ->whereDate('attendance_date', $attendanceDate)
            ->where('session_slot', 'daily')
            ->first();

        if (! $session) {
            $session = $this->studentSessions->create(\App\DataTransferObjects\Attendance\StudentAttendanceSessionData::fromArray([
                'school_id' => $student->school_id,
                'academic_year_id' => $enrollment->academic_year_id,
                'school_class_id' => $enrollment->school_class_id,
                'section_id' => $enrollment->section_id,
                'attendance_date' => $attendanceDate,
                'session_type' => 'daily',
                'attendance_period_id' => null,
                'subject_id' => null,
                'teacher_id' => null,
                'status' => 'draft',
                'marked_by' => 1,
                'submitted_at' => null,
                'locked_at' => null,
            ]));
        }

        $this->studentSessions->bulkMark($session, [[
            'student_id' => $student->id,
            'attendance_status_type_id' => $presentStatusId,
            'check_in_time' => $log->log_type === 'check_in' ? $log->log_datetime?->format('H:i') : null,
            'check_out_time' => $log->log_type === 'check_out' ? $log->log_datetime?->format('H:i') : null,
            'remarks' => 'Processed from biometric log.',
        ]], 1);
    }
}
