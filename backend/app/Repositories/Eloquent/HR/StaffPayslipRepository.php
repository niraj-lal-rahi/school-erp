<?php

namespace App\Repositories\Eloquent\HR;

use App\Models\HR\PayrollRun;
use App\Models\HR\StaffPayslip;
use App\Repositories\Contracts\HR\StaffPayslipRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffPayslipRepository implements StaffPayslipRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffPayslip::query()
            ->with(['staff', 'payrollRun'])
            ->when($filters['payroll_run_id'] ?? null, fn ($query, int|string $runId) => $query->where('payroll_run_id', $runId))
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['payment_status'] ?? null, fn ($query, string $status) => $query->where('payment_status', $status))
            ->orderByDesc('id')
            ->get();
    }

    public function findOrFail(int $id): StaffPayslip
    {
        return StaffPayslip::query()->with(['staff', 'payrollRun'])->findOrFail($id);
    }

    public function create(PayrollRun $run, array $attributes): StaffPayslip
    {
        return $run->payslips()->create($attributes + ['school_id' => $run->school_id]);
    }

    public function update(StaffPayslip $payslip, array $attributes): StaffPayslip
    {
        $payslip->update($attributes);

        return $this->findOrFail($payslip->id);
    }

    public function delete(StaffPayslip $payslip): void
    {
        $payslip->delete();
    }
}
