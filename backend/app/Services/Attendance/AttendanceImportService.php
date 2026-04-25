<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendanceImportData;
use App\Events\Attendance\AttendanceImportQueued;
use App\Jobs\Attendance\ProcessAttendanceImportJob;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceImport;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\Attendance\AttendanceImportRepositoryInterface;
use App\Services\HR\StaffAttendanceService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceImportService
{
    public function __construct(
        protected AttendanceImportRepositoryInterface $imports,
        protected StudentAttendanceSessionService $studentSessions,
        protected StaffAttendanceService $staffAttendance,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->imports->all($filters);
    }

    public function create(AttendanceImportData $data): AttendanceImport
    {
        $rows = $data->attributes['rows'] ?? [];

        return DB::transaction(fn (): AttendanceImport => $this->imports->create(AttendanceImportData::fromArray([
            'school_id' => $data->attributes['school_id'],
            'file_path' => $data->attributes['file_path'] ?? null,
            'import_type' => $data->attributes['import_type'],
            'import_date' => $data->attributes['import_date'],
            'status' => 'pending',
            'total_records' => count($rows),
            'success_count' => 0,
            'failed_count' => 0,
            'logs' => ['payload_rows' => $rows, 'messages' => []],
            'uploaded_by' => $data->attributes['uploaded_by'],
        ])));
    }

    public function queueProcess(AttendanceImport $attendanceImport): AttendanceImport
    {
        $attendanceImport = $this->imports->update($attendanceImport, AttendanceImportData::fromArray([
            ...$attendanceImport->toArray(),
            'status' => 'processing',
        ]));

        event(new AttendanceImportQueued($attendanceImport));

        if (app()->runningUnitTests()) {
            ProcessAttendanceImportJob::dispatchSync($attendanceImport->id);
        } else {
            ProcessAttendanceImportJob::dispatch($attendanceImport->id);
        }

        return $attendanceImport->refresh();
    }

    public function process(AttendanceImport $attendanceImport): AttendanceImport
    {
        $rows = $attendanceImport->logs['payload_rows'] ?? [];
        $messages = [];
        $success = 0;
        $failed = 0;

        foreach ($rows as $index => $row) {
            try {
                if ($attendanceImport->import_type === 'staff') {
                    $this->processStaffRow($attendanceImport->school_id, $row, $attendanceImport->uploaded_by);
                } else {
                    $this->processStudentRow($attendanceImport->school_id, $row, $attendanceImport->uploaded_by);
                }

                $success++;
            } catch (\Throwable $throwable) {
                $failed++;
                $messages[] = [
                    'row' => $index + 1,
                    'message' => $throwable->getMessage(),
                ];
            }
        }

        return $this->imports->update($attendanceImport, AttendanceImportData::fromArray([
            ...$attendanceImport->toArray(),
            'status' => $failed > 0 ? 'failed' : 'completed',
            'success_count' => $success,
            'failed_count' => $failed,
            'logs' => [
                'messages' => $messages,
                'payload_rows' => $rows,
            ],
        ]));
    }

    protected function processStaffRow(int $schoolId, array $row, int $markedBy): void
    {
        $staff = isset($row['staff_id'])
            ? Staff::query()->findOrFail((int) $row['staff_id'])
            : Staff::query()->where('employee_code', $row['employee_code'] ?? '')->firstOrFail();

        $this->staffAttendance->create($staff, \App\DataTransferObjects\HR\StaffAttendanceData::fromArray([
            'attendance_date' => $row['attendance_date'],
            'attendance_status_type_id' => $this->resolveStatusTypeId($schoolId, $row),
            'attendance_status' => $row['attendance_status'] ?? null,
            'check_in_time' => $row['check_in_time'] ?? null,
            'check_out_time' => $row['check_out_time'] ?? null,
            'source' => $row['source'] ?? 'import',
            'remarks' => $row['remarks'] ?? 'Imported attendance record.',
        ]), $markedBy);
    }

    protected function processStudentRow(int $schoolId, array $row, int $markedBy): void
    {
        $student = isset($row['student_id'])
            ? Student::query()->findOrFail((int) $row['student_id'])
            : Student::query()->where('admission_no', $row['admission_no'] ?? '')->firstOrFail();

        $attendanceDate = $row['attendance_date'];
        $academicYearId = $row['academic_year_id']
            ?? AcademicYear::query()
                ->whereDate('start_date', '<=', $attendanceDate)
                ->whereDate('end_date', '>=', $attendanceDate)
                ->value('id');
        $enrollment = StudentEnrollment::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('is_current', true)
            ->firstOrFail();
        $session = StudentAttendanceSession::query()
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->where('school_class_id', $enrollment->school_class_id)
            ->where('section_id', $enrollment->section_id)
            ->whereDate('attendance_date', $attendanceDate)
            ->where('session_slot', 'daily')
            ->first();

        if (! $session) {
            $session = $this->studentSessions->create(\App\DataTransferObjects\Attendance\StudentAttendanceSessionData::fromArray([
                'school_id' => $schoolId,
                'academic_year_id' => $enrollment->academic_year_id,
                'school_class_id' => $enrollment->school_class_id,
                'section_id' => $enrollment->section_id,
                'attendance_date' => $attendanceDate,
                'session_type' => 'daily',
                'attendance_period_id' => null,
                'subject_id' => null,
                'teacher_id' => null,
                'status' => 'draft',
                'marked_by' => $markedBy,
                'submitted_at' => null,
                'locked_at' => null,
            ]));
        }

        $this->studentSessions->bulkMark($session, [[
            'student_id' => $student->id,
            'attendance_status_type_id' => $this->resolveStatusTypeId($schoolId, $row),
            'check_in_time' => $row['check_in_time'] ?? null,
            'check_out_time' => $row['check_out_time'] ?? null,
            'remarks' => $row['remarks'] ?? 'Imported attendance record.',
        ]], $markedBy);
    }

    protected function resolveStatusTypeId(int $schoolId, array $row): int
    {
        if (! empty($row['attendance_status_type_id'])) {
            return (int) $row['attendance_status_type_id'];
        }

        $code = strtoupper((string) ($row['attendance_status'] ?? $row['status_code'] ?? 'PRESENT'));

        return (int) AttendanceStatusType::query()
            ->where('school_id', $schoolId)
            ->where('code', $code)
            ->value('id');
    }
}
