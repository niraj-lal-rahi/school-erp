<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Repositories\Contracts\StudentStatusHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentStatusHistoryRepository implements StudentStatusHistoryRepositoryInterface
{
    public function create(Student $student, array $attributes): StudentStatusHistory
    {
        return $student->statusHistory()->create($attributes);
    }

    public function allForStudent(Student $student): Collection
    {
        return StudentStatusHistory::query()
            ->with('performer')
            ->where('student_id', $student->id)
            ->latest('effective_date')
            ->latest('id')
            ->get();
    }
}
