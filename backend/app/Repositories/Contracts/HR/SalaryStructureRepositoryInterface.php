<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\SalaryStructureData;
use App\Models\HR\SalaryStructure;
use App\Models\HR\Staff;
use Illuminate\Database\Eloquent\Collection;

interface SalaryStructureRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): SalaryStructure;

    public function create(Staff $staff, SalaryStructureData $data): SalaryStructure;

    public function update(SalaryStructure $structure, SalaryStructureData $data): SalaryStructure;

    public function delete(SalaryStructure $structure): void;

    public function activeForPayrollMonth(int $schoolId, int $month, int $year): Collection;
}
