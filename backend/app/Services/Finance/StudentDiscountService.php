<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\StudentDiscountData;
use App\Enums\Finance\StudentDiscountStatus;
use App\Models\Finance\StudentDiscount;
use App\Repositories\Contracts\Finance\StudentDiscountRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentDiscountService
{
    public function __construct(
        protected StudentDiscountRepositoryInterface $studentDiscounts,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentDiscounts->paginate($filters, $perPage);
    }

    public function create(StudentDiscountData $data): StudentDiscount
    {
        return DB::transaction(fn (): StudentDiscount => $this->studentDiscounts->create($data));
    }

    public function update(StudentDiscount $studentDiscount, StudentDiscountData $data): StudentDiscount
    {
        if ($studentDiscount->status !== StudentDiscountStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending student discounts can be updated.',
            ]);
        }

        return DB::transaction(fn (): StudentDiscount => $this->studentDiscounts->update($studentDiscount, $data));
    }

    public function delete(StudentDiscount $studentDiscount): void
    {
        DB::transaction(fn (): bool => tap($studentDiscount)->delete());
    }

    public function approve(StudentDiscount $studentDiscount, int $approvedBy): StudentDiscount
    {
        if ($studentDiscount->status !== StudentDiscountStatus::Pending->value) {
            throw ValidationException::withMessages([
                'status' => 'Only pending student discounts can be approved.',
            ]);
        }

        return DB::transaction(fn (): StudentDiscount => $this->studentDiscounts->update($studentDiscount, StudentDiscountData::fromArray([
            ...$studentDiscount->only(['school_id', 'student_id', 'academic_year_id', 'discount_type_id', 'fee_head_id', 'discount_amount', 'reason']),
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => StudentDiscountStatus::Approved->value,
        ])));
    }

    public function reject(StudentDiscount $studentDiscount): StudentDiscount
    {
        return DB::transaction(fn (): StudentDiscount => $this->studentDiscounts->update($studentDiscount, StudentDiscountData::fromArray([
            ...$studentDiscount->only(['school_id', 'student_id', 'academic_year_id', 'discount_type_id', 'fee_head_id', 'discount_amount', 'reason', 'approved_by', 'approved_at']),
            'status' => StudentDiscountStatus::Rejected->value,
        ])));
    }
}
