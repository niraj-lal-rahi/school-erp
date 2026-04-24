<?php

namespace App\Services\HR;

use App\Enums\HR\PayslipPaymentStatus;
use App\Models\HR\StaffPayslip;
use App\Repositories\Contracts\HR\StaffPayslipRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffPayslipService
{
    public function __construct(
        protected StaffPayslipRepositoryInterface $payslips,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->payslips->all($filters);
    }

    public function show(StaffPayslip $payslip): StaffPayslip
    {
        return $this->payslips->findOrFail($payslip->id);
    }

    public function update(StaffPayslip $payslip, array $attributes): StaffPayslip
    {
        return DB::transaction(fn (): StaffPayslip => $this->payslips->update($payslip, $attributes));
    }

    public function markPaid(StaffPayslip $payslip, ?string $remarks = null): StaffPayslip
    {
        return DB::transaction(fn (): StaffPayslip => $this->payslips->update($payslip, [
            'payment_status' => PayslipPaymentStatus::Paid->value,
            'paid_at' => now(),
            'remarks' => $remarks ?? $payslip->remarks,
        ]));
    }
}
