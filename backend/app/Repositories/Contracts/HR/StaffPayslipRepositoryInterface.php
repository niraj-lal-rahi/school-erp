<?php

namespace App\Repositories\Contracts\HR;

use App\Models\HR\PayrollRun;
use App\Models\HR\StaffPayslip;
use Illuminate\Database\Eloquent\Collection;

interface StaffPayslipRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): StaffPayslip;

    public function create(PayrollRun $run, array $attributes): StaffPayslip;

    public function update(StaffPayslip $payslip, array $attributes): StaffPayslip;

    public function delete(StaffPayslip $payslip): void;
}
