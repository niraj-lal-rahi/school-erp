<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantBillingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'subscription_id' => $this->subscription_id,
            'invoice_no' => $this->invoice_no,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'billing_date' => optional($this->billing_date)?->toDateString(),
            'due_date' => optional($this->due_date)?->toDateString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'status' => $this->status,
            'payment_reference' => $this->payment_reference,
            'metadata' => $this->metadata,
            'subscription' => $this->whenLoaded('subscription', fn () => $this->subscription ? new TenantSubscriptionResource($this->subscription) : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
