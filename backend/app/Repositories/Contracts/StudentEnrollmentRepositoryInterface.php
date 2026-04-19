<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\EnrollmentData;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentEnrollmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): StudentEnrollment;

    public function create(EnrollmentData $data): StudentEnrollment;

    public function update(StudentEnrollment $enrollment, EnrollmentData $data): StudentEnrollment;

    public function delete(StudentEnrollment $enrollment): void;

    public function currentForStudent(Student $student): ?StudentEnrollment;

    public function allForStudent(Student $student): Collection;
}
