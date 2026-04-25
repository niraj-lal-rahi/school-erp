<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceSessionData;
use App\Models\Attendance\StudentAttendanceSession;
use App\Repositories\Contracts\Attendance\StudentAttendanceSessionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentAttendanceSessionRepository implements StudentAttendanceSessionRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StudentAttendanceSession::query()
            ->with(['academicYear', 'schoolClass', 'section', 'period', 'subject', 'teacher', 'marker'])
            ->withCount('records')
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $id) => $query->where('academic_year_id', $id))
            ->when($filters['class_id'] ?? null, fn ($query, int|string $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn ($query, int|string $id) => $query->where('section_id', $id))
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $id) => $query->where('teacher_id', $id))
            ->when($filters['subject_id'] ?? null, fn ($query, int|string $id) => $query->where('subject_id', $id))
            ->when($filters['session_type'] ?? null, fn ($query, string $type) => $query->where('session_type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['attendance_date_from'] ?? null, fn ($query, string $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when($filters['attendance_date_to'] ?? null, fn ($query, string $date) => $query->whereDate('attendance_date', '<=', $date))
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(StudentAttendanceSessionData $data): StudentAttendanceSession
    {
        return StudentAttendanceSession::create($data->attributes);
    }

    public function update(StudentAttendanceSession $session, StudentAttendanceSessionData $data): StudentAttendanceSession
    {
        $session->update($data->attributes);

        return $this->refreshWithRelations($session);
    }

    public function delete(StudentAttendanceSession $session): void
    {
        $session->delete();
    }

    public function refreshWithRelations(StudentAttendanceSession $session): StudentAttendanceSession
    {
        return $session->refresh()->load(['academicYear', 'schoolClass', 'section', 'period', 'subject', 'teacher', 'marker', 'records.student', 'records.attendanceStatus']);
    }
}
