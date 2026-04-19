<?php

namespace App\Events\SIS;

use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Student $student,
    ) {
    }
}
