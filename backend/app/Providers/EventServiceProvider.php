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
use App\Events\Examination\ResultsPublished;
use App\Events\Finance\InvoicePaid;
use App\Events\Finance\PaymentSuccessful;
use App\Events\Finance\ReceiptGenerated;
use App\Events\Payments\PaymentFailed as GatewayPaymentFailed;
use App\Events\Payments\PaymentInitiated;
use App\Events\Payments\PaymentRefunded;
use App\Events\Payments\PaymentSuccessful as GatewayPaymentSuccessful;
use App\Events\Payments\UpiPaymentManuallyVerified;
use App\Events\Payments\UpiPaymentPendingVerification;
use App\Events\Reports\ReportRunCompleted;
use App\Events\SIS\StudentCreated;
use App\Events\Timetable\TimetableVersionPublished;
use App\Events\Workflows\AutomationRuleTriggered;
use App\Events\Workflows\ReminderDue;
use App\Events\Workflows\WorkflowCompleted;
use App\Events\Workflows\WorkflowStarted;
use App\Events\Workflows\WorkflowStepApproved;
use App\Events\Workflows\WorkflowStepRejected;
use App\Listeners\Auth\UpdateLastLoginAt;
use App\Listeners\Attendance\LogStudentAttendanceSessionLocked;
use App\Listeners\Attendance\LogStudentAttendanceSessionSubmitted;
use App\Listeners\Attendance\LogAttendanceImportQueued;
use App\Listeners\Attendance\LogBiometricLogsQueued;
use App\Listeners\Communication\HandleNotificationFailure;
use App\Listeners\Communication\LogMessageSent;
use App\Listeners\Communication\QueueAnnouncementNotifications;
use App\Listeners\Examination\SendResultNotifications;
use App\Listeners\Finance\GenerateReceiptForSuccessfulPayment;
use App\Listeners\Finance\LogInvoicePaid;
use App\Listeners\Finance\LogReceiptGenerated;
use App\Listeners\Payments\GenerateReceipt;
use App\Listeners\Payments\SendPaymentNotification;
use App\Listeners\Payments\SyncFinancePayment;
use App\Listeners\Payments\SyncSaaSBilling;
use App\Listeners\Reports\CacheReportResults;
use App\Listeners\Reports\SendScheduledReport;
use App\Listeners\SIS\DispatchStudentProvisioningWorkflow;
use App\Listeners\Timetable\LogTimetableVersionPublished;
use App\Listeners\Workflows\LogWorkflowActivity;
use App\Listeners\Workflows\SendWorkflowNotification;
use App\Listeners\Workflows\StartWorkflowFromEvent;
use App\Listeners\Workflows\TriggerAutomationFromEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            UpdateLastLoginAt::class,
        ],
        StudentAttendanceSessionSubmitted::class => [
            LogStudentAttendanceSessionSubmitted::class,
            StartWorkflowFromEvent::class,
            TriggerAutomationFromEvent::class,
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
            StartWorkflowFromEvent::class,
            TriggerAutomationFromEvent::class,
        ],
        MessageSent::class => [
            LogMessageSent::class,
        ],
        NotificationFailed::class => [
            HandleNotificationFailure::class,
        ],
        ResultsPublished::class => [
            SendResultNotifications::class,
            StartWorkflowFromEvent::class,
            TriggerAutomationFromEvent::class,
        ],
        PaymentSuccessful::class => [
            GenerateReceiptForSuccessfulPayment::class,
        ],
        PaymentInitiated::class => [
            SendPaymentNotification::class,
        ],
        GatewayPaymentSuccessful::class => [
            SyncFinancePayment::class,
            SyncSaaSBilling::class,
            SendPaymentNotification::class,
            GenerateReceipt::class,
        ],
        GatewayPaymentFailed::class => [
            SendPaymentNotification::class,
        ],
        PaymentRefunded::class => [
            SyncFinancePayment::class,
            SyncSaaSBilling::class,
            SendPaymentNotification::class,
        ],
        UpiPaymentPendingVerification::class => [
            SendPaymentNotification::class,
        ],
        UpiPaymentManuallyVerified::class => [
            SendPaymentNotification::class,
        ],
        InvoicePaid::class => [
            LogInvoicePaid::class,
            StartWorkflowFromEvent::class,
            TriggerAutomationFromEvent::class,
        ],
        ReceiptGenerated::class => [
            LogReceiptGenerated::class,
        ],
        ReportRunCompleted::class => [
            SendScheduledReport::class,
            CacheReportResults::class,
        ],
        TimetableVersionPublished::class => [
            LogTimetableVersionPublished::class,
        ],
        StudentCreated::class => [
            DispatchStudentProvisioningWorkflow::class,
            StartWorkflowFromEvent::class,
            TriggerAutomationFromEvent::class,
        ],
        WorkflowStarted::class => [
            SendWorkflowNotification::class,
            LogWorkflowActivity::class,
        ],
        WorkflowStepApproved::class => [
            SendWorkflowNotification::class,
            LogWorkflowActivity::class,
        ],
        WorkflowStepRejected::class => [
            SendWorkflowNotification::class,
            LogWorkflowActivity::class,
        ],
        WorkflowCompleted::class => [
            SendWorkflowNotification::class,
            LogWorkflowActivity::class,
        ],
        AutomationRuleTriggered::class => [
            LogWorkflowActivity::class,
        ],
        ReminderDue::class => [
            SendWorkflowNotification::class,
            LogWorkflowActivity::class,
        ],
    ];
}
