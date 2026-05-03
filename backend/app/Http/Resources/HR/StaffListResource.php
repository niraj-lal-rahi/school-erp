<?php

namespace App\Http\Resources\HR;

use App\Http\Resources\Concerns\SupportsIncludes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffListResource extends JsonResource
{
    use SupportsIncludes;

    public function toArray(Request $request): array
    {
        return $this->applySparseFieldset($request, [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'staff_type' => $this->staff_type,
            'employment_type' => $this->employment_type,
            'current_status' => $this->current_status,
            'joining_date' => optional($this->joining_date)->toDateString(),
            'department' => $this->includeRequested($request, 'department') && $this->relationLoaded('department')
                ? [
                    'id' => $this->department?->id,
                    'name' => $this->department?->name,
                    'code' => $this->department?->code,
                ]
                : null,
            'designation' => $this->includeRequested($request, 'designation') && $this->relationLoaded('designation')
                ? [
                    'id' => $this->designation?->id,
                    'name' => $this->designation?->name,
                    'code' => $this->designation?->code,
                ]
                : null,
            'user' => $this->includeRequested($request, 'user') && $this->relationLoaded('user')
                ? [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ]
                : null,
            'created_at' => optional($this->created_at)->toAtomString(),
        ]);
    }
}
