<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceRecordData;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\Attendance\StudentAttendanceSession;
use App\Repositories\Contracts\Attendance\StudentAttendanceRecordRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentAttendanceRecordRepository implements StudentAttendanceRecordRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StudentAttendanceRecord::query()
            ->with(['session.schoolClass', 'session.section', 'student', 'attendanceStatus', 'marker'])
            ->when($filters['student_id'] ?? null, fn ($query, int|string $id) => $query->where('student_id', $id))
            ->when($filters['attendance_session_id'] ?? null, fn ($query, int|string $id) => $query->where('attendance_session_id', $id))
            ->when($filters['attendance_status_type_id'] ?? null, fn ($query, int|string $id) => $query->where('attendance_status_type_id', $id))
            ->when($filters['attendance_date_from'] ?? null, function ($query, string $date): void {
                $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->whereDate('attendance_date', '>=', $date));
            })
            ->when($filters['attendance_date_to'] ?? null, function ($query, string $date): void {
                $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->whereDate('attendance_date', '<=', $date));
            })
            ->orderByDesc('id')
            ->get();
    }

    public function create(StudentAttendanceRecordData $data): StudentAttendanceRecord
    {
        return StudentAttendanceRecord::create($data->attributes);
    }

    public function upsertForSession(StudentAttendanceSession $session, array $records, int $schoolId, int $markedBy): void
    {
        foreach ($records as $record) {
            $attendanceRecord = StudentAttendanceRecord::query()
                ->withTrashed()
                ->firstOrNew([
                    'attendance_session_id' => $session->id,
                    'student_id' => $record['student_id'],
                ]);

            if ($attendanceRecord->trashed()) {
                $attendanceRecord->restore();
            }

            $attendanceRecord->fill([
                'school_id' => $schoolId,
                'attendance_status_type_id' => $record['attendance_status_type_id'],
                'check_in_time' => $record['check_in_time'] ?? null,
                'check_out_time' => $record['check_out_time'] ?? null,
                'remarks' => $record['remarks'] ?? null,
                'marked_by' => $markedBy,
            ]);
            $attendanceRecord->save();
        }
    }

    public function update(StudentAttendanceRecord $record, StudentAttendanceRecordData $data): StudentAttendanceRecord
    {
        $record->update($data->attributes);

        return $record->refresh()->load(['student', 'attendanceStatus', 'marker', 'session']);
    }

    public function delete(StudentAttendanceRecord $record): void
    {
        $record->delete();
    }
}
