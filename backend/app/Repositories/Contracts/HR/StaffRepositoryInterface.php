<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffData;
use App\Models\HR\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StaffRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Staff;

    public function create(StaffData $data): Staff;

    public function update(Staff $staff, StaffData $data): Staff;

    public function delete(Staff $staff): void;
}
