<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'billing_cycle' => $this->billing_cycle,
            'start_date' => optional($this->start_date)?->toDateString(),
            'end_date' => optional($this->end_date)?->toDateString(),
            'trial_ends_at' => optional($this->trial_ends_at)?->toAtomString(),
            'status' => $this->status,
            'auto_renew' => (bool) $this->auto_renew,
            'plan' => $this->whenLoaded('subscriptionPlan', fn () => $this->subscriptionPlan ? new SubscriptionPlanResource($this->subscriptionPlan) : null),
            'billing_records_count' => $this->whenCounted('billingRecords'),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
