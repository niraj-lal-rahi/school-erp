<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantBillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'subscription_id' => $this->subscription_id,
            'invoice_no' => $this->invoice_no,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'billing_date' => optional($this->billing_date)?->toDateString(),
            'due_date' => optional($this->due_date)?->toDateString(),
            'paid_at' => optional($this->paid_at)?->toAtomString(),
            'status' => $this->status,
            'subscription' => $this->whenLoaded('subscription', fn () => $this->subscription ? new TenantSubscriptionResource($this->subscription) : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
