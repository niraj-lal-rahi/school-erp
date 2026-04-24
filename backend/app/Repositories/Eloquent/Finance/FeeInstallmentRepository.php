<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\FeeInstallmentData;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\StudentFeeAssignment;
use App\Repositories\Contracts\Finance\FeeInstallmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FeeInstallmentRepository implements FeeInstallmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $installmentQuery) use ($search): void {
                    $installmentQuery->where('installment_name', 'like', "%{$search}%")
                        ->orWhereHas('studentFeeAssignment.student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('studentFeeAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('student_id', $value)))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('studentFeeAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('academic_year_id', $value)))
            ->when($filters['school_class_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('studentFeeAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('school_class_id', $value)))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->whereHas('studentFeeAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('section_id', $value)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['due_date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('due_date', '>=', $value))
            ->when($filters['due_date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('due_date', '<=', $value))
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): FeeInstallment
    {
        return $this->query()->findOrFail($id);
    }

    public function create(FeeInstallmentData $data): FeeInstallment
    {
        $installment = FeeInstallment::create($data->attributes);

        return $this->findOrFail($installment->id);
    }

    public function update(FeeInstallment $feeInstallment, FeeInstallmentData $data): FeeInstallment
    {
        $feeInstallment->update($data->attributes);

        return $this->findOrFail($feeInstallment->id);
    }

    public function delete(FeeInstallment $feeInstallment): void
    {
        $feeInstallment->delete();
    }

    public function createMany(StudentFeeAssignment $assignment, array $rows): Collection
    {
        $assignment->installments()->createMany($rows);

        return $assignment->installments()->with(['feeHead', 'studentFeeAssignment.student'])->orderBy('due_date')->get();
    }

    public function existsForAssignment(StudentFeeAssignment $assignment): bool
    {
        return FeeInstallment::query()
            ->where('student_fee_assignment_id', $assignment->id)
            ->exists();
    }

    protected function query(): Builder
    {
        return FeeInstallment::query()->with([
            'feeHead',
            'studentFeeAssignment.student',
            'studentFeeAssignment.academicYear',
            'studentFeeAssignment.schoolClass',
            'studentFeeAssignment.section',
            'studentFeeAssignment.feeStructure',
        ]);
    }
}
