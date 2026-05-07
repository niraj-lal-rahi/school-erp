<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanFeatureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
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
            'config' => $this->config,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
