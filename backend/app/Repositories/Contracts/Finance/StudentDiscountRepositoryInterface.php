<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\StudentDiscountData;
use App\Models\Finance\StudentDiscount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentDiscountRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(StudentDiscountData $data): StudentDiscount;

    public function update(StudentDiscount $studentDiscount, StudentDiscountData $data): StudentDiscount;

    public function delete(StudentDiscount $studentDiscount): void;

    public function nextApprovedAmountForStudentDiscount(StudentDiscount $studentDiscount): float;
}
