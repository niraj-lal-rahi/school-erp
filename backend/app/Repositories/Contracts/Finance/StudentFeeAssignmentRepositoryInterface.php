<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\StudentFeeAssignmentData;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentFeeAssignmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(StudentFeeAssignmentData $data): StudentFeeAssignment;

    public function update(StudentFeeAssignment $assignment, StudentFeeAssignmentData $data): StudentFeeAssignment;

    public function delete(StudentFeeAssignment $assignment): void;

    public function activeForStudentAcademicYear(Student $student, int $academicYearId, ?int $ignoreId = null): ?StudentFeeAssignment;
}
