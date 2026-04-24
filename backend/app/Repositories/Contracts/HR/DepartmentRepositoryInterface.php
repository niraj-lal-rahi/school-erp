<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\DepartmentData;
use App\Models\HR\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(DepartmentData $data): Department;

    public function update(Department $department, DepartmentData $data): Department;

    public function delete(Department $department): void;
}
