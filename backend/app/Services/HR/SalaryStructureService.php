<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\SalaryStructureData;
use App\Enums\HR\SalaryCalculationType;
use App\Enums\HR\SalaryComponentType;
use App\Models\HR\SalaryStructure;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\SalaryStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalaryStructureService
{
    public function __construct(
        protected SalaryStructureRepositoryInterface $structures,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->structures->all($filters);
    }

    public function show(SalaryStructure $structure): SalaryStructure
    {
        return $this->structures->findOrFail($structure->id);
    }

    public function create(Staff $staff, SalaryStructureData $data): SalaryStructure
    {
        return DB::transaction(function () use ($staff, $data): SalaryStructure {
            $payload = $this->hydrateTotals($data);

            return $this->structures->create($staff, $payload);
        });
    }

    public function update(SalaryStructure $structure, SalaryStructureData $data): SalaryStructure
    {
        return DB::transaction(function () use ($structure, $data): SalaryStructure {
            $payload = $this->hydrateTotals($data);

            return $this->structures->update($structure, $payload);
        });
    }

    public function delete(SalaryStructure $structure): void
    {
        DB::transaction(fn (): bool => $structure->delete());
    }

    protected function hydrateTotals(SalaryStructureData $data): SalaryStructureData
    {
        $attributes = $data->attributes;
        $basic = (float) ($attributes['basic_salary'] ?? 0);
        $earnings = 0.0;
        $deductions = 0.0;

        foreach ($data->items as $item) {
            $amount = (float) ($item['amount'] ?? 0);

            if (($item['percentage'] ?? null) !== null && ($item['amount'] ?? null) === null) {
                $amount = round($basic * ((float) $item['percentage'] / 100), 2);
            }

            if (($item['component_type'] ?? null) === SalaryComponentType::Deduction->value) {
                $deductions += $amount;
            } else {
                $earnings += $amount;
            }
        }

        $attributes['gross_salary'] = round($basic + $earnings, 2);
        $attributes['net_salary'] = round($attributes['gross_salary'] - $deductions, 2);

        return SalaryStructureData::fromArray([
            ...$attributes,
            'items' => collect($data->items)->map(function (array $item) use ($basic): array {
                if (($item['percentage'] ?? null) !== null && ($item['amount'] ?? null) === null) {
                    $item['amount'] = round($basic * ((float) $item['percentage'] / 100), 2);
                }

                return $item;
            })->all(),
        ]);
    }
}
