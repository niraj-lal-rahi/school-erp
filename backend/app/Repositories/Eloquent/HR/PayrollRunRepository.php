<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\PayrollRunData;
use App\Models\HR\PayrollRun;
use App\Repositories\Contracts\HR\PayrollRunRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PayrollRunRepository implements PayrollRunRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return PayrollRun::query()
            ->with(['processor'])
            ->when($filters['payroll_month'] ?? null, fn ($query, int|string $month) => $query->where('payroll_month', $month))
            ->when($filters['payroll_year'] ?? null, fn ($query, int|string $year) => $query->where('payroll_year', $year))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('payroll_year')
            ->orderByDesc('payroll_month')
            ->get();
    }

    public function findOrFail(int $id): PayrollRun
    {
        return PayrollRun::query()->with(['payslips.staff', 'processor'])->findOrFail($id);
    }

    public function create(PayrollRunData $data): PayrollRun
    {
        return PayrollRun::create($data->attributes);
    }

    public function update(PayrollRun $run, PayrollRunData $data): PayrollRun
    {
        $run->update($data->attributes);

        return $this->findOrFail($run->id);
    }

    public function delete(PayrollRun $run): void
    {
        $run->delete();
    }
}
