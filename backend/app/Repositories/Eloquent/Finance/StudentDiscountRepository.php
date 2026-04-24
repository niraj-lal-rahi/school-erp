<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\StudentDiscountData;
use App\Models\Finance\StudentDiscount;
use App\Repositories\Contracts\Finance\StudentDiscountRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudentDiscountRepository implements StudentDiscountRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(StudentDiscountData $data): StudentDiscount
    {
        return StudentDiscount::create($data->attributes);
    }

    public function update(StudentDiscount $studentDiscount, StudentDiscountData $data): StudentDiscount
    {
        $studentDiscount->update($data->attributes);

        return $this->query()->findOrFail($studentDiscount->id);
    }

    public function delete(StudentDiscount $studentDiscount): void
    {
        $studentDiscount->delete();
    }

    public function nextApprovedAmountForStudentDiscount(StudentDiscount $studentDiscount): float
    {
        return (float) $studentDiscount->discount_amount;
    }

    protected function query(): Builder
    {
        return StudentDiscount::query()->with(['student', 'academicYear', 'discountType', 'feeHead', 'approver']);
    }
}
