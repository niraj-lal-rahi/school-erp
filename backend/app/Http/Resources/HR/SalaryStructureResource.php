<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryStructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'effective_from' => optional($this->effective_from)->toDateString(),
            'effective_to' => optional($this->effective_to)->toDateString(),
            'basic_salary' => $this->basic_salary,
            'gross_salary' => $this->gross_salary,
            'net_salary' => $this->net_salary,
            'status' => $this->status,
            'staff' => $this->whenLoaded('staff', fn () => [
                'id' => $this->staff?->id,
                'employee_code' => $this->staff?->employee_code,
                'full_name' => $this->staff?->full_name,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'salary_component_id' => $item->salary_component_id,
                'amount' => $item->amount,
                'percentage' => $item->percentage,
                'salary_component' => $item->salaryComponent ? [
                    'id' => $item->salaryComponent->id,
                    'name' => $item->salaryComponent->name,
                    'code' => $item->salaryComponent->code,
                    'component_type' => $item->salaryComponent->component_type,
                ] : null,
            ])->values()),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
