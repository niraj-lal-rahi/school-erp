<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use App\Models\StudentStatusHistory;
use Illuminate\Database\Eloquent\Collection;

interface StudentStatusHistoryRepositoryInterface
{
    public function create(Student $student, array $attributes): StudentStatusHistory;

    public function allForStudent(Student $student): Collection;
}
