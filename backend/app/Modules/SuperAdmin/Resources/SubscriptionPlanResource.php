<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'currency' => $this->currency,
            'max_students' => $this->max_students,
            'max_staff' => $this->max_staff,
            'max_storage_mb' => $this->max_storage_mb,
            'status' => $this->status,
            'features_count' => $this->whenCounted('features'),
            'subscriptions_count' => $this->whenCounted('subscriptions'),
            'features' => PlanFeatureResource::collection($this->whenLoaded('features')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
