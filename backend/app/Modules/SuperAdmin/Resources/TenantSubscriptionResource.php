<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantSubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'subscription_code' => $this->subscription_code,
            'billing_cycle' => $this->billing_cycle,
            'start_date' => optional($this->start_date)?->toDateString(),
            'end_date' => optional($this->end_date)?->toDateString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'status' => $this->status,
            'auto_renew' => (bool) $this->auto_renew,
            'next_billing_at' => $this->next_billing_at?->toISOString(),
            'plan' => $this->whenLoaded('subscriptionPlan', fn () => $this->subscriptionPlan ? new SubscriptionPlanResource($this->subscriptionPlan) : null),
            'billing_records_count' => $this->whenCounted('billingRecords'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
