<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\Attendance\StudentAttendanceSessionLocked;
use App\Events\Attendance\StudentAttendanceSessionSubmitted;
use App\Events\Attendance\AttendanceImportQueued;
use App\Events\Attendance\BiometricLogsQueued;
use App\Events\Communication\AnnouncementPublished;
use App\Events\Communication\MessageSent;
use App\Events\Communication\NotificationFailed;
use App\Events\Communication\ScheduledMessageProcessed;
use App\Events\Finance\InvoicePaid;
use App\Events\Finance\PaymentSuccessful;
use App\Events\Finance\ReceiptGenerated;
use App\Events\SIS\StudentCreated;
use App\Events\Timetable\TimetableVersionPublished;
use App\Listeners\Auth\UpdateLastLoginAt;
use App\Listeners\Attendance\LogStudentAttendanceSessionLocked;
use App\Listeners\Attendance\LogStudentAttendanceSessionSubmitted;
use App\Listeners\Attendance\LogAttendanceImportQueued;
use App\Listeners\Attendance\LogBiometricLogsQueued;
use App\Listeners\Communication\HandleNotificationFailure;
use App\Listeners\Communication\LogMessageSent;
use App\Listeners\Communication\QueueAnnouncementNotifications;
use App\Listeners\Finance\GenerateReceiptForSuccessfulPayment;
use App\Listeners\Finance\LogInvoicePaid;
use App\Listeners\Finance\LogReceiptGenerated;
use App\Listeners\SIS\DispatchStudentProvisioningWorkflow;
use App\Listeners\Timetable\LogTimetableVersionPublished;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            UpdateLastLoginAt::class,
        ],
        StudentAttendanceSessionSubmitted::class => [
            LogStudentAttendanceSessionSubmitted::class,
        ],
        StudentAttendanceSessionLocked::class => [
            LogStudentAttendanceSessionLocked::class,
        ],
        AttendanceImportQueued::class => [
            LogAttendanceImportQueued::class,
        ],
        BiometricLogsQueued::class => [
            LogBiometricLogsQueued::class,
        ],
        AnnouncementPublished::class => [
            QueueAnnouncementNotifications::class,
        ],
        MessageSent::class => [
            LogMessageSent::class,
        ],
        NotificationFailed::class => [
            HandleNotificationFailure::class,
        ],
        PaymentSuccessful::class => [
            GenerateReceiptForSuccessfulPayment::class,
        ],
        InvoicePaid::class => [
            LogInvoicePaid::class,
        ],
        ReceiptGenerated::class => [
            LogReceiptGenerated::class,
        ],
        TimetableVersionPublished::class => [
            LogTimetableVersionPublished::class,
        ],
        StudentCreated::class => [
            DispatchStudentProvisioningWorkflow::class,
        ],
    ];
}
