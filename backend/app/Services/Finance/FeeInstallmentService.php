<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FeeInstallmentData;
use App\Enums\Finance\FeeDueFrequency;
use App\Enums\Finance\FeeInstallmentStatus;
use App\Models\AcademicYear;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentFeeAssignment;
use App\Repositories\Contracts\Finance\FeeInstallmentRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeInstallmentService
{
    public function __construct(
        protected FeeInstallmentRepositoryInterface $installments,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->installments->paginate($filters, $perPage);
    }

    public function create(FeeInstallmentData $data): FeeInstallment
    {
        $attributes = $this->normalizeAttributes($data->attributes);

        return DB::transaction(fn (): FeeInstallment => $this->installments->create(FeeInstallmentData::fromArray($attributes)));
    }

    public function update(FeeInstallment $feeInstallment, FeeInstallmentData $data): FeeInstallment
    {
        $attributes = $this->normalizeAttributes([
            ...$feeInstallment->only(['school_id', 'student_fee_assignment_id', 'fee_head_id']),
            ...$data->attributes,
        ]);

        return DB::transaction(fn (): FeeInstallment => $this->installments->update($feeInstallment, FeeInstallmentData::fromArray($attributes)));
    }

    public function delete(FeeInstallment $feeInstallment): void
    {
        DB::transaction(fn (): bool => $feeInstallment->delete());
    }

    public function generateForAssignment(StudentFeeAssignment $assignment): Collection
    {
        $assignment->loadMissing(['academicYear', 'feeStructure.items.feeHead']);

        if ($this->installments->existsForAssignment($assignment)) {
            throw ValidationException::withMessages([
                'student_fee_assignment_id' => 'Installments have already been generated for this assignment.',
            ]);
        }

        if (! $assignment->feeStructure || $assignment->feeStructure->items->isEmpty()) {
            throw ValidationException::withMessages([
                'fee_structure_id' => 'The selected assignment does not have a fee structure with items.',
            ]);
        }

        $rows = [];
        foreach ($assignment->feeStructure->items as $item) {
            foreach ($this->buildSchedule($assignment->academicYear, $assignment->feeStructure, $assignment->assigned_date, $item->due_frequency, $item->due_day) as $schedule) {
                $rows[] = [
                    'school_id' => $assignment->school_id,
                    'fee_head_id' => $item->fee_head_id,
                    'installment_name' => $item->feeHead?->name.' - '.$schedule['label'],
                    'due_date' => $schedule['date']->toDateString(),
                    'amount' => $item->amount,
                    'discount_amount' => 0,
                    'fine_amount' => 0,
                    'paid_amount' => 0,
                    'balance_amount' => $item->amount,
                    'status' => FeeInstallmentStatus::Pending->value,
                ];
            }
        }

        return DB::transaction(fn (): Collection => $this->installments->createMany($assignment, $rows));
    }

    protected function normalizeAttributes(array $attributes): array
    {
        $amount = (float) ($attributes['amount'] ?? 0);
        $discount = (float) ($attributes['discount_amount'] ?? 0);
        $fine = (float) ($attributes['fine_amount'] ?? 0);
        $paid = (float) ($attributes['paid_amount'] ?? 0);
        $net = max(0, $amount - $discount + $fine);
        $balance = max(0, $net - $paid);
        $status = $attributes['status'] ?? null;

        if (! $status) {
            $status = $paid <= 0
                ? FeeInstallmentStatus::Pending->value
                : ($balance <= 0 ? FeeInstallmentStatus::Paid->value : FeeInstallmentStatus::PartiallyPaid->value);
        }

        return [
            ...$attributes,
            'discount_amount' => $discount,
            'fine_amount' => $fine,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'status' => $status,
        ];
    }

    protected function buildSchedule(
        AcademicYear $academicYear,
        FeeStructure $feeStructure,
        Carbon|string $assignedDate,
        string $frequency,
        ?int $dueDay,
    ): array {
        $start = Carbon::parse($academicYear->start_date)->startOfDay();
        $end = Carbon::parse($academicYear->end_date)->endOfDay();
        $assignmentDate = Carbon::parse($assignedDate)->startOfDay();
        $start = $assignmentDate->greaterThan($start) ? $assignmentDate : $start;

        if ($feeStructure->effective_from) {
            $effectiveFrom = Carbon::parse($feeStructure->effective_from)->startOfDay();
            $start = $effectiveFrom->greaterThan($start) ? $effectiveFrom : $start;
        }

        if ($feeStructure->effective_to) {
            $effectiveTo = Carbon::parse($feeStructure->effective_to)->endOfDay();
            $end = $effectiveTo->lessThan($end) ? $effectiveTo : $end;
        }

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'fee_structure_id' => 'The fee structure effective dates do not overlap the academic year.',
            ]);
        }

        return match ($frequency) {
            FeeDueFrequency::Monthly->value => $this->periodSchedule($start, $end, '1 month', $dueDay, 'M Y'),
            FeeDueFrequency::Quarterly->value => $this->periodSchedule($start, $end, '3 months', $dueDay, '"Q".q Y'),
            FeeDueFrequency::HalfYearly->value => $this->periodSchedule($start, $end, '6 months', $dueDay, '"H".'.(int) ceil(((int) date('n')) / 6).' Y'),
            FeeDueFrequency::Yearly->value => [[
                'date' => $this->alignedDueDate($start, $dueDay),
                'label' => $academicYear->name,
            ]],
            FeeDueFrequency::Custom->value => [[
                'date' => $this->alignedDueDate($start, $dueDay),
                'label' => 'Custom',
            ]],
            default => [[
                'date' => $this->alignedDueDate($start, $dueDay),
                'label' => 'One Time',
            ]],
        };
    }

    protected function periodSchedule(Carbon $start, Carbon $end, string $interval, ?int $dueDay, string $labelFormat): array
    {
        $period = CarbonPeriod::create($start->copy()->startOfMonth(), $interval, $end->copy()->startOfMonth());
        $schedule = [];

        foreach ($period as $anchor) {
            $date = $this->alignedDueDate(Carbon::instance($anchor), $dueDay);
            if ($date->lt($start)) {
                $date = $start->copy();
            }

            if ($date->gt($end)) {
                continue;
            }

            $label = str_contains($labelFormat, '"Q"')
                ? 'Q'.(int) ceil($anchor->quarter).' '.$anchor->format('Y')
                : (str_contains($labelFormat, '"H"')
                    ? 'H'.($anchor->month <= 6 ? 1 : 2).' '.$anchor->format('Y')
                    : $anchor->format($labelFormat));

            $schedule[] = [
                'date' => $date,
                'label' => $label,
            ];
        }

        return $schedule;
    }

    protected function alignedDueDate(Carbon $date, ?int $dueDay): Carbon
    {
        if (! $dueDay) {
            return $date->copy();
        }

        $aligned = $date->copy()->day(min($dueDay, $date->daysInMonth));

        return $aligned;
    }
}
