<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\DesignationData;
use App\Models\HR\Designation;
use Illuminate\Database\Eloquent\Collection;

interface DesignationRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(DesignationData $data): Designation;

    public function update(Designation $designation, DesignationData $data): Designation;

    public function delete(Designation $designation): void;
}
