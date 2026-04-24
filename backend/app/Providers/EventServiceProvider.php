<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\Finance\InvoicePaid;
use App\Events\Finance\PaymentSuccessful;
use App\Events\Finance\ReceiptGenerated;
use App\Events\SIS\StudentCreated;
use App\Listeners\Auth\UpdateLastLoginAt;
use App\Listeners\Finance\GenerateReceiptForSuccessfulPayment;
use App\Listeners\Finance\LogInvoicePaid;
use App\Listeners\Finance\LogReceiptGenerated;
use App\Listeners\SIS\DispatchStudentProvisioningWorkflow;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            UpdateLastLoginAt::class,
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
        StudentCreated::class => [
            DispatchStudentProvisioningWorkflow::class,
        ],
    ];
}
