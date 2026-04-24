<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FeeInstallmentData;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\StudentFeeAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FeeInstallmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): FeeInstallment;

    public function create(FeeInstallmentData $data): FeeInstallment;

    public function update(FeeInstallment $feeInstallment, FeeInstallmentData $data): FeeInstallment;

    public function delete(FeeInstallment $feeInstallment): void;

    public function createMany(StudentFeeAssignment $assignment, array $rows): Collection;

    public function existsForAssignment(StudentFeeAssignment $assignment): bool;
}
