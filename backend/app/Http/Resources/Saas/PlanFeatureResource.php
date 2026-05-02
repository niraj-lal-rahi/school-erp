<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'feature_code' => $this->feature_code,
            'feature_name' => $this->feature_name,
            'module' => $this->module,
            'is_enabled' => (bool) $this->is_enabled,
            'limit_value' => $this->limit_value,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
