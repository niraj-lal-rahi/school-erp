<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'max_students' => $this->max_students,
            'max_staff' => $this->max_staff,
            'max_storage_mb' => $this->max_storage_mb,
            'current_students' => $this->current_students,
            'current_staff' => $this->current_staff,
            'current_storage_mb' => $this->current_storage_mb,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
