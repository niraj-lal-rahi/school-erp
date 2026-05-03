<?php

namespace App\Listeners\Workflows;

use App\Events\Attendance\StudentAttendanceSessionSubmitted;
use App\Events\Communication\AnnouncementPublished;
use App\Events\Examination\ResultsPublished;
use App\Events\Finance\InvoicePaid;
use App\Events\SIS\StudentCreated;
use App\Models\Admission;
use App\Models\Finance\FeeInvoice;
use App\Models\HR\StaffLeaveApplication;
use App\Services\Workflows\TriggerResolverService;
use Illuminate\Contracts\Queue\ShouldQueue;

class StartWorkflowFromEvent implements ShouldQueue
{
    public function __construct(
        protected TriggerResolverService $triggers,
    ) {
    }

    public function handle(object $event): void
    {
        [$eventName, $payload] = $this->mapEvent($event);

        if (! $eventName) {
            return;
        }

        $this->triggers->startMatchingWorkflows($eventName, $payload);
    }

    protected function mapEvent(object $event): array
    {
        return match (true) {
            $event instanceof StudentCreated => [
                'student.admission.approved',
                [
                    'school_id' => $event->student->school_id,
                    'reference_type' => \App\Models\Student::class,
                    'reference_id' => $event->student->id,
                    'model_class' => \App\Models\Student::class,
                    'student_id' => $event->student->id,
                ],
            ],
            $event instanceof InvoicePaid => [
                'fee.invoice.created',
                [
                    'school_id' => $event->invoice->school_id,
                    'reference_type' => FeeInvoice::class,
                    'reference_id' => $event->invoice->id,
                    'model_class' => FeeInvoice::class,
                    'invoice_id' => $event->invoice->id,
                    'student_id' => $event->invoice->student_id,
                ],
            ],
            $event instanceof StudentAttendanceSessionSubmitted => [
                'attendance.student.absent',
                [
                    'school_id' => $event->session->school_id,
                    'reference_type' => get_class($event->session),
                    'reference_id' => $event->session->id,
                    'model_class' => get_class($event->session),
                    'attendance_session_id' => $event->session->id,
                ],
            ],
            $event instanceof ResultsPublished => [
                'exam.result.published',
                [
                    'school_id' => $event->publication->school_id,
                    'reference_type' => get_class($event->publication->exam),
                    'reference_id' => $event->publication->exam_id,
                    'model_class' => get_class($event->publication->exam),
                    'exam_id' => $event->publication->exam_id,
                ],
            ],
            $event instanceof AnnouncementPublished => [
                'communication.announcement.published',
                [
                    'school_id' => $event->announcement->school_id,
                    'reference_type' => get_class($event->announcement),
                    'reference_id' => $event->announcement->id,
                    'model_class' => get_class($event->announcement),
                ],
            ],
            default => [null, []],
        };
    }
}
