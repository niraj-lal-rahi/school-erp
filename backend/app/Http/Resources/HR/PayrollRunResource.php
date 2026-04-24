<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_month' => $this->payroll_month,
            'payroll_year' => $this->payroll_year,
            'status' => $this->status,
            'total_gross' => $this->total_gross,
            'total_deductions' => $this->total_deductions,
            'total_net' => $this->total_net,
            'processed_by' => $this->processed_by,
            'processed_at' => optional($this->processed_at)->toAtomString(),
            'payslips' => StaffPayslipResource::collection($this->whenLoaded('payslips')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
