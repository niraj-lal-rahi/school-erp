<?php

namespace App\Http\Resources\Payments;

use App\Http\Resources\Concerns\SupportsIncludes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentListResource extends JsonResource
{
    use SupportsIncludes;

    public function toArray(Request $request): array
    {
        return $this->applySparseFieldset($request, [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'student_id' => $this->student_id,
            'provider' => $this->provider,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'paid_at' => optional($this->paid_at)?->toAtomString(),
            'student' => $this->includeRequested($request, 'student') && $this->relationLoaded('student')
                ? [
                    'id' => $this->student?->id,
                    'full_name' => $this->student?->full_name,
                    'admission_no' => $this->student?->admission_no,
                ]
                : null,
            'gateway' => $this->includeRequested($request, 'gateway') && $this->relationLoaded('gateway')
                ? [
                    'id' => $this->gateway?->id,
                    'name' => $this->gateway?->name,
                    'provider' => $this->gateway?->provider,
                    'mode' => $this->gateway?->mode,
                ]
                : null,
            'tenant_subscription' => $this->includeRequested($request, 'tenantSubscription') && $this->relationLoaded('tenantSubscription')
                ? [
                    'id' => $this->tenantSubscription?->id,
                    'subscription_plan_id' => $this->tenantSubscription?->subscription_plan_id,
                    'billing_cycle' => $this->tenantSubscription?->billing_cycle,
                    'status' => $this->tenantSubscription?->status,
                ]
                : null,
            'created_at' => optional($this->created_at)?->toAtomString(),
        ]);
    }
}
