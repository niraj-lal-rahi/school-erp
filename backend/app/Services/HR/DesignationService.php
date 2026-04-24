<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\DesignationData;
use App\Models\HR\Designation;
use App\Repositories\Contracts\HR\DesignationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DesignationService
{
    public function __construct(
        protected DesignationRepositoryInterface $designations,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->designations->all($filters);
    }

    public function create(DesignationData $data): Designation
    {
        return DB::transaction(fn (): Designation => $this->designations->create($data));
    }

    public function update(Designation $designation, DesignationData $data): Designation
    {
        return DB::transaction(fn (): Designation => $this->designations->update($designation, $data));
    }

    public function delete(Designation $designation): void
    {
        DB::transaction(fn (): bool => $designation->delete());
    }
}
