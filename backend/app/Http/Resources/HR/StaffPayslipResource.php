<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffPayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_run_id' => $this->payroll_run_id,
            'staff_id' => $this->staff_id,
            'gross_salary' => $this->gross_salary,
            'total_deductions' => $this->total_deductions,
            'net_salary' => $this->net_salary,
            'earnings_breakdown' => $this->earnings_breakdown,
            'deductions_breakdown' => $this->deductions_breakdown,
            'payment_status' => $this->payment_status,
            'paid_at' => optional($this->paid_at)->toAtomString(),
            'remarks' => $this->remarks,
            'staff' => $this->whenLoaded('staff', fn () => [
                'id' => $this->staff?->id,
                'employee_code' => $this->staff?->employee_code,
                'full_name' => $this->staff?->full_name,
            ]),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
