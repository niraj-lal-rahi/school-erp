<?php

namespace App\Services\Portal;

use App\Models\AcademicManagement\HomeworkAssignment;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Communication\Announcement;
use App\Models\Examination\StudentResult;
use App\Models\Finance\FeeInvoice;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\Timetable\TimetableEntry;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\User;
use App\Repositories\Contracts\Portal\PortalAccessRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PortalStudentDataService
{
    public function __construct(
        protected PortalAccessRepositoryInterface $accesses,
    ) {
    }

    public function overview(User $user, int $studentId): array
    {
        $student = $this->studentWithEnrollment($studentId);
        $access = $this->assertAccess($user, $studentId);

        return [
            'student' => $this->formatStudent($student),
            'attendance' => $this->attendance($user, $studentId),
            'fees' => $this->fees($user, $studentId),
            'results' => $this->results($user, $studentId),
            'timetable' => [
                'upcoming' => $this->timetable($user, $studentId)['upcoming'],
            ],
            'announcements' => [
                'unread_count' => $this->announcementCount($student),
            ],
            'permissions' => $this->formatPermissions($access),
        ];
    }

    public function attendance(User $user, int $studentId): array
    {
        $access = $this->assertAccess($user, $studentId, 'can_view_attendance');

        $summary = AttendanceSummary::query()
            ->where('user_type', 'student')
            ->where('user_id', $studentId)
            ->latest('id')
            ->first();

        return [
            'summary' => $summary ? [
                'total_days' => (int) $summary->total_days,
                'present_days' => (int) $summary->present_days,
                'absent_days' => (int) $summary->absent_days,
                'leave_days' => (int) $summary->leave_days,
                'late_days' => (int) $summary->late_days,
                'percentage' => (float) $summary->percentage,
                'last_updated_at' => optional($summary->last_updated_at)->toIso8601String(),
            ] : null,
            'permissions' => $this->formatPermissions($access),
        ];
    }

    public function fees(User $user, int $studentId): array
    {
        $access = $this->assertAccess($user, $studentId, 'can_view_fees');

        $query = FeeInvoice::query()
            ->where('student_id', $studentId)
            ->whereNotIn('status', ['cancelled'])
            ->with(['academicYear', 'payments']);

        $invoices = $query->latest('issue_date')->get();

        return [
            'summary' => [
                'total_invoiced' => round((float) $invoices->sum('grand_total'), 2),
                'total_paid' => round((float) $invoices->sum('paid_amount'), 2),
                'total_due' => round((float) $invoices->sum('balance_amount'), 2),
                'can_pay_fees' => (bool) $access->can_pay_fees,
            ],
            'invoices' => $invoices->map(fn (FeeInvoice $invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'issue_date' => optional($invoice->issue_date)->toDateString(),
                'due_date' => optional($invoice->due_date)->toDateString(),
                'grand_total' => (float) $invoice->grand_total,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance_amount' => (float) $invoice->balance_amount,
                'status' => $invoice->status,
            ])->values()->all(),
        ];
    }

    public function results(User $user, int $studentId): array
    {
        $this->assertAccess($user, $studentId, 'can_view_results');

        $results = StudentResult::query()
            ->where('student_id', $studentId)
            ->with(['exam.examType', 'resultSubjectDetails.subject'])
            ->latest('computed_at')
            ->get();

        return [
            'latest_result' => $results->first() ? $this->formatResult($results->first()) : null,
            'results' => $results->map(fn (StudentResult $result) => $this->formatResult($result))->values()->all(),
        ];
    }

    public function timetable(User $user, int $studentId): array
    {
        $student = $this->studentWithEnrollment($studentId);
        $enrollment = $student->enrollments->firstWhere('is_current', true);

        if (! $enrollment) {
            return ['upcoming' => [], 'weekly' => []];
        }

        $today = strtolower(Carbon::now()->format('l'));

        $entries = TimetableEntry::query()
            ->where('school_class_id', $enrollment->school_class_id)
            ->where(function (Builder $query) use ($enrollment): void {
                if ($enrollment->section_id) {
                    $query->where('section_id', $enrollment->section_id)
                        ->orWhereNull('section_id');

                    return;
                }

                $query->whereNull('section_id');
            })
            ->where('status', 'active')
            ->with(['period', 'subject', 'staff', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('attendance_period_id')
            ->get();

        return [
            'upcoming' => $entries->where('day_of_week', $today)->values()->map(fn ($entry) => $this->formatTimetableEntry($entry))->all(),
            'weekly' => $entries->groupBy('day_of_week')->map(
                fn ($dayRows) => $dayRows->map(fn ($entry) => $this->formatTimetableEntry($entry))->values()->all()
            )->all(),
        ];
    }

    public function assignments(User $user, int $studentId): array
    {
        $student = $this->studentWithEnrollment($studentId);
        $enrollment = $student->enrollments->firstWhere('is_current', true);

        if (! $enrollment) {
            return ['assignments' => []];
        }

        $assignments = HomeworkAssignment::query()
            ->where('school_class_id', $enrollment->school_class_id)
            ->when($enrollment->section_id, fn ($query, $sectionId) => $query->where(function ($inner) use ($sectionId): void {
                $inner->where('section_id', $sectionId)->orWhereNull('section_id');
            }))
            ->where('status', 'published')
            ->with(['subject', 'staff'])
            ->latest('assigned_date')
            ->limit(20)
            ->get();

        return [
            'assignments' => $assignments->map(fn (HomeworkAssignment $assignment) => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'assigned_date' => optional($assignment->assigned_date)->toDateString(),
                'due_date' => optional($assignment->due_date)->toDateString(),
                'subject' => $assignment->subject?->name,
                'teacher' => $assignment->staff?->full_name,
                'attachment_path' => $assignment->attachment_path,
            ])->values()->all(),
        ];
    }

    public function transport(User $user, int $studentId): array
    {
        $allocation = StudentTransportAllocation::query()
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->latest('allocated_from')
            ->with(['route', 'routeAssignment.vehicle', 'pickupStop', 'dropStop'])
            ->first();

        if (! $allocation) {
            return ['allocation' => null];
        }

        return [
            'allocation' => [
                'id' => $allocation->id,
                'route' => $allocation->route ? [
                    'id' => $allocation->route->id,
                    'name' => $allocation->route->name,
                    'code' => $allocation->route->code,
                ] : null,
                'vehicle' => $allocation->routeAssignment?->vehicle ? [
                    'id' => $allocation->routeAssignment->vehicle->id,
                    'vehicle_no' => $allocation->routeAssignment->vehicle->vehicle_no,
                    'registration_no' => $allocation->routeAssignment->vehicle->registration_no,
                ] : null,
                'pickup_stop' => $allocation->pickupStop?->name,
                'drop_stop' => $allocation->dropStop?->name,
                'fare_amount' => (float) $allocation->fare_amount,
                'allocated_from' => optional($allocation->allocated_from)->toDateString(),
                'allocated_to' => optional($allocation->allocated_to)->toDateString(),
            ],
        ];
    }

    public function documents(User $user, int $studentId): array
    {
        $this->assertAccess($user, $studentId, 'can_view_documents');

        $documents = StudentDocument::query()
            ->where('student_id', $studentId)
            ->latest('issued_date')
            ->get();

        return [
            'documents' => $documents->map(fn (StudentDocument $document) => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'title' => $document->title,
                'file_name' => $document->file_name,
                'file_path' => $document->file_path,
                'mime_type' => $document->mime_type,
                'file_size' => $document->file_size,
                'issued_date' => optional($document->issued_date)->toDateString(),
                'expiry_date' => optional($document->expiry_date)->toDateString(),
                'verification_status' => $document->verification_status,
            ])->values()->all(),
        ];
    }

    public function assertAccess(User $user, int $studentId, ?string $permissionColumn = null): PortalProfileAccess
    {
        $access = $this->accesses->getActiveAccessesByUser($user->id)
            ->first(fn ($row) => (int) $row->student_id === $studentId);

        if (! $access) {
            throw ValidationException::withMessages([
                'student_id' => 'The selected student is not accessible in this portal account.',
            ]);
        }

        if ($permissionColumn && ! (bool) $access->{$permissionColumn}) {
            throw ValidationException::withMessages([
                'student_id' => 'This portal profile does not have permission for the requested student data.',
            ]);
        }

        return $access;
    }

    protected function studentWithEnrollment(int $studentId): Student
    {
        return Student::query()
            ->with(['enrollments' => fn ($query) => $query->latest('id')])
            ->findOrFail($studentId);
    }

    protected function formatStudent(Student $student): array
    {
        $enrollment = $student->enrollments->firstWhere('is_current', true);

        return [
            'id' => $student->id,
            'full_name' => $student->full_name,
            'admission_no' => $student->admission_no,
            'roll_no' => $student->roll_no,
            'email' => $student->email,
            'phone' => $student->phone,
            'class_id' => $enrollment?->school_class_id,
            'section_id' => $enrollment?->section_id,
            'academic_year_id' => $enrollment?->academic_year_id,
        ];
    }

    protected function formatResult(StudentResult $result): array
    {
        return [
            'id' => $result->id,
            'exam' => $result->exam ? [
                'id' => $result->exam->id,
                'name' => $result->exam->name,
                'code' => $result->exam->code,
                'exam_type' => $result->exam->examType?->name,
            ] : null,
            'total_marks' => (float) $result->total_marks,
            'obtained_marks' => (float) $result->obtained_marks,
            'percentage' => (float) $result->percentage,
            'grade' => $result->grade,
            'gpa' => $result->gpa !== null ? (float) $result->gpa : null,
            'result_status' => $result->result_status,
            'rank' => $result->rank,
            'computed_at' => optional($result->computed_at)->toIso8601String(),
            'subjects' => $result->resultSubjectDetails->map(fn ($detail) => [
                'subject_id' => $detail->subject_id,
                'subject' => $detail->subject?->name,
                'max_marks' => (float) $detail->max_marks,
                'obtained_marks' => (float) $detail->obtained_marks,
                'grade' => $detail->grade,
                'is_pass' => (bool) $detail->is_pass,
            ])->values()->all(),
        ];
    }

    protected function formatTimetableEntry($entry): array
    {
        return [
            'id' => $entry->id,
            'day_of_week' => $entry->day_of_week,
            'entry_type' => $entry->entry_type,
            'period' => $entry->period ? [
                'id' => $entry->period->id,
                'name' => $entry->period->name,
                'start_time' => $entry->period->start_time,
                'end_time' => $entry->period->end_time,
            ] : null,
            'subject' => $entry->subject?->name,
            'teacher' => $entry->staff?->full_name,
            'room' => $entry->room?->name,
            'notes' => $entry->notes,
        ];
    }

    protected function announcementCount(Student $student): int
    {
        $enrollment = $student->enrollments->firstWhere('is_current', true);

        return Announcement::query()
            ->where('status', 'published')
            ->where(function (Builder $query) use ($student, $enrollment): void {
                $query->whereIn('audience_type', ['all', 'students'])
                    ->orWhere(function (Builder $classQuery) use ($enrollment): void {
                        if (! $enrollment) {
                            $classQuery->whereRaw('1 = 0');

                            return;
                        }

                        $classQuery->where('audience_type', 'class')
                            ->where('class_id', $enrollment->school_class_id);
                    })
                    ->orWhere(function (Builder $sectionQuery) use ($enrollment): void {
                        if (! $enrollment || ! $enrollment->section_id) {
                            $sectionQuery->whereRaw('1 = 0');

                            return;
                        }

                        $sectionQuery->where('audience_type', 'section')
                            ->where('class_id', $enrollment->school_class_id)
                            ->where('section_id', $enrollment->section_id);
                    })
                    ->orWhereHas('recipients', fn (Builder $recipientQuery) => $recipientQuery
                        ->where('recipient_type', 'student')
                        ->where('recipient_id', $student->id));
            })
            ->count();
    }

    protected function formatPermissions(PortalProfileAccess $access): array
    {
        return [
            'can_view_attendance' => (bool) $access->can_view_attendance,
            'can_view_fees' => (bool) $access->can_view_fees,
            'can_pay_fees' => (bool) $access->can_pay_fees,
            'can_view_results' => (bool) $access->can_view_results,
            'can_view_documents' => (bool) $access->can_view_documents,
            'can_message_teacher' => (bool) $access->can_message_teacher,
        ];
    }
}
