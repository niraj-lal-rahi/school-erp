<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\AdmissionData;
use App\Models\Admission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdmissionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Admission;

    public function create(AdmissionData $data): Admission;

    public function update(Admission $admission, AdmissionData $data): Admission;

    public function delete(Admission $admission): void;
}
