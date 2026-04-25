<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendanceCorrectionData;
use App\Models\Attendance\AttendanceCorrection;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\HR\StaffAttendance;
use App\Repositories\Contracts\Attendance\AttendanceCorrectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCorrectionService
{
    public function __construct(
        protected AttendanceCorrectionRepositoryInterface $corrections,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->corrections->all($filters);
    }

    public function create(AttendanceCorrectionData $data): AttendanceCorrection
    {
        $attributes = $data->attributes;
        $attributes['old_status_id'] = $this->resolveOldStatusId($attributes);
        $attributes['status'] = $attributes['status'] ?? 'pending';

        return DB::transaction(fn (): AttendanceCorrection => $this->corrections->create(AttendanceCorrectionData::fromArray($attributes)));
    }

    public function approve(AttendanceCorrection $correction, ?string $remarks, int $approvedBy): AttendanceCorrection
    {
        if ($correction->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only pending corrections can be approved.'],
            ]);
        }

        return DB::transaction(function () use ($correction, $remarks, $approvedBy): AttendanceCorrection {
            $newStatus = AttendanceStatusType::query()->findOrFail($correction->new_status_id);
            $normalizedStatus = strtolower($newStatus->code);

            if ($correction->reference_type === 'student') {
                StudentAttendanceRecord::query()
                    ->where('student_id', $correction->reference_id)
                    ->whereHas('session', fn ($query) => $query->whereDate('attendance_date', $correction->attendance_date))
                    ->update([
                        'attendance_status_type_id' => $newStatus->id,
                    ]);
            } else {
                StaffAttendance::query()
                    ->where('staff_id', $correction->reference_id)
                    ->whereDate('attendance_date', $correction->attendance_date)
                    ->update([
                        'attendance_status_type_id' => $newStatus->id,
                        'attendance_status' => $normalizedStatus,
                    ]);
            }

            return $this->corrections->update($correction, AttendanceCorrectionData::fromArray([
                'school_id' => $correction->school_id,
                'reference_type' => $correction->reference_type,
                'reference_id' => $correction->reference_id,
                'attendance_date' => $correction->attendance_date?->toDateString(),
                'old_status_id' => $correction->old_status_id,
                'new_status_id' => $correction->new_status_id,
                'reason' => $correction->reason,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'status' => 'approved',
                'review_remarks' => $remarks,
            ]));
        });
    }

    public function reject(AttendanceCorrection $correction, ?string $remarks, int $approvedBy): AttendanceCorrection
    {
        if ($correction->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only pending corrections can be rejected.'],
            ]);
        }

        return DB::transaction(fn (): AttendanceCorrection => $this->corrections->update($correction, AttendanceCorrectionData::fromArray([
            'school_id' => $correction->school_id,
            'reference_type' => $correction->reference_type,
            'reference_id' => $correction->reference_id,
            'attendance_date' => $correction->attendance_date?->toDateString(),
            'old_status_id' => $correction->old_status_id,
            'new_status_id' => $correction->new_status_id,
            'reason' => $correction->reason,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => 'rejected',
            'review_remarks' => $remarks,
        ])));
    }

    protected function resolveOldStatusId(array $attributes): ?int
    {
        if (($attributes['reference_type'] ?? null) === 'student') {
            return StudentAttendanceRecord::query()
                ->where('student_id', $attributes['reference_id'])
                ->whereHas('session', fn ($query) => $query->whereDate('attendance_date', $attributes['attendance_date']))
                ->value('attendance_status_type_id');
        }

        return StaffAttendance::query()
            ->where('staff_id', $attributes['reference_id'])
            ->whereDate('attendance_date', $attributes['attendance_date'])
            ->value('attendance_status_type_id');
    }
}
