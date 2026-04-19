<?php

namespace App\Listeners\SIS;

use App\Events\SIS\StudentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DispatchStudentProvisioningWorkflow implements ShouldQueue
{
    public function handle(StudentCreated $event): void
    {
        Log::info('Student provisioning workflow dispatched.', [
            'student_id' => $event->student->id,
            'school_id' => $event->student->school_id,
        ]);
    }
}
