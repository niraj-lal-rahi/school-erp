<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\DepartmentData;
use App\Models\HR\Department;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function __construct(
        protected DepartmentRepositoryInterface $departments,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->departments->all($filters);
    }

    public function create(DepartmentData $data): Department
    {
        return DB::transaction(fn (): Department => $this->departments->create($data));
    }

    public function update(Department $department, DepartmentData $data): Department
    {
        return DB::transaction(fn (): Department => $this->departments->update($department, $data));
    }

    public function delete(Department $department): void
    {
        DB::transaction(fn (): bool => $department->delete());
    }
}
