<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
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
            'features_count' => $this->whenCounted('planFeatures'),
            'subscriptions_count' => $this->whenCounted('tenantSubscriptions'),
            'features' => PlanFeatureResource::collection($this->whenLoaded('planFeatures')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
