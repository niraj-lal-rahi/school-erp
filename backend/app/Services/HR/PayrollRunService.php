<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\PayrollRunData;
use App\Enums\HR\PayrollRunStatus;
use App\Enums\HR\PayslipPaymentStatus;
use App\Enums\HR\SalaryComponentType;
use App\Models\HR\PayrollRun;
use App\Models\HR\StaffPayslip;
use App\Repositories\Contracts\HR\PayrollRunRepositoryInterface;
use App\Repositories\Contracts\HR\SalaryStructureRepositoryInterface;
use App\Repositories\Contracts\HR\StaffPayslipRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PayrollRunService
{
    public function __construct(
        protected PayrollRunRepositoryInterface $runs,
        protected SalaryStructureRepositoryInterface $structures,
        protected StaffPayslipRepositoryInterface $payslips,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->runs->all($filters);
    }

    public function show(PayrollRun $run): PayrollRun
    {
        return $this->runs->findOrFail($run->id);
    }

    public function create(PayrollRunData $data): PayrollRun
    {
        return DB::transaction(fn (): PayrollRun => $this->runs->create($data));
    }

    public function update(PayrollRun $run, PayrollRunData $data): PayrollRun
    {
        return DB::transaction(fn (): PayrollRun => $this->runs->update($run, $data));
    }

    public function delete(PayrollRun $run): void
    {
        DB::transaction(fn (): bool => $run->delete());
    }

    public function process(PayrollRun $run, int $processedBy): PayrollRun
    {
        return DB::transaction(function () use ($run, $processedBy): PayrollRun {
            $run = $this->runs->update($run, PayrollRunData::fromArray([
                'status' => PayrollRunStatus::Processing->value,
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]));

            $run->payslips()->delete();
            $structures = $this->structures->activeForPayrollMonth($run->school_id, $run->payroll_month, $run->payroll_year);

            $totalGross = 0.0;
            $totalDeductions = 0.0;
            $totalNet = 0.0;

            foreach ($structures as $structure) {
                $earnings = [];
                $deductions = [];

                foreach ($structure->items as $item) {
                    $row = [
                        'component_id' => $item->salary_component_id,
                        'component_name' => $item->salaryComponent?->name,
                        'amount' => (float) $item->amount,
                    ];

                    if ($item->salaryComponent?->component_type === SalaryComponentType::Deduction->value) {
                        $deductions[] = $row;
                    } else {
                        $earnings[] = $row;
                    }
                }

                $gross = (float) ($structure->gross_salary ?? 0);
                $deductionTotal = collect($deductions)->sum('amount');
                $net = (float) ($structure->net_salary ?? ($gross - $deductionTotal));

                $this->payslips->create($run, [
                    'staff_id' => $structure->staff_id,
                    'gross_salary' => $gross,
                    'total_deductions' => $deductionTotal,
                    'net_salary' => $net,
                    'earnings_breakdown' => $earnings,
                    'deductions_breakdown' => $deductions,
                    'payment_status' => PayslipPaymentStatus::Unpaid->value,
                    'remarks' => null,
                ]);

                $totalGross += $gross;
                $totalDeductions += $deductionTotal;
                $totalNet += $net;
            }

            return $this->runs->update($run, PayrollRunData::fromArray([
                'status' => PayrollRunStatus::Processing->value,
                'processed_by' => $processedBy,
                'processed_at' => now(),
                'total_gross' => round($totalGross, 2),
                'total_deductions' => round($totalDeductions, 2),
                'total_net' => round($totalNet, 2),
            ]));
        });
    }

    public function finalize(PayrollRun $run, int $processedBy): PayrollRun
    {
        return DB::transaction(function () use ($run, $processedBy): PayrollRun {
            if ($run->payslips()->count() === 0) {
                $run = $this->process($run, $processedBy);
            }

            return $this->runs->update($run, PayrollRunData::fromArray([
                'status' => PayrollRunStatus::Finalized->value,
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]));
        });
    }

    public function markPaid(PayrollRun $run, int $processedBy): PayrollRun
    {
        return DB::transaction(function () use ($run, $processedBy): PayrollRun {
            $run->payslips()->update([
                'payment_status' => PayslipPaymentStatus::Paid->value,
                'paid_at' => now(),
            ]);

            return $this->runs->update($run, PayrollRunData::fromArray([
                'status' => PayrollRunStatus::Paid->value,
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]));
        });
    }
}
