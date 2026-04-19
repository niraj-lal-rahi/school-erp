<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\SIS\StudentCreated;
use App\Listeners\Auth\UpdateLastLoginAt;
use App\Listeners\SIS\DispatchStudentProvisioningWorkflow;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            UpdateLastLoginAt::class,
        ],
        StudentCreated::class => [
            DispatchStudentProvisioningWorkflow::class,
        ],
    ];
}
