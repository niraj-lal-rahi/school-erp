<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'transaction_no' => $this->transaction_no,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'student_id' => $this->student_id,
            'tenant_subscription_id' => $this->tenant_subscription_id,
            'gateway_id' => $this->gateway_id,
            'provider' => $this->provider,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'gateway_order_id' => $this->gateway_order_id,
            'gateway_payment_id' => $this->gateway_payment_id,
            'upi_vpa' => $this->upi_vpa,
            'upi_reference_no' => $this->upi_reference_no,
            'upi_qr_payload' => $this->upi_qr_payload,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'paid_at' => optional($this->paid_at)?->toAtomString(),
            'verified_at' => optional($this->verified_at)?->toAtomString(),
            'verified_by' => $this->verified_by,
            'failure_reason' => $this->failure_reason,
            'metadata' => $this->metadata,
            'gateway' => $this->whenLoaded('gateway', fn () => $this->gateway ? [
                'id' => $this->gateway->id,
                'name' => $this->gateway->name,
                'code' => $this->gateway->code,
                'provider' => $this->gateway->provider,
                'mode' => $this->gateway->mode,
                'status' => $this->gateway->status,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
                'roll_no' => $this->student->roll_no,
            ] : null),
            'tenant_subscription' => $this->whenLoaded('tenantSubscription', fn () => $this->tenantSubscription ? [
                'id' => $this->tenantSubscription->id,
                'subscription_plan_id' => $this->tenantSubscription->subscription_plan_id,
                'billing_cycle' => $this->tenantSubscription->billing_cycle,
                'status' => $this->tenantSubscription->status,
            ] : null),
            'verifier' => $this->whenLoaded('verifier', fn () => $this->verifier ? [
                'id' => $this->verifier->id,
                'name' => $this->verifier->name,
                'email' => $this->verifier->email,
            ] : null),
            'refunds' => PaymentRefundResource::collection($this->whenLoaded('refunds')),
            'reconciliations' => PaymentReconciliationResource::collection($this->whenLoaded('reconciliations')),
            'upi_payment_request' => $this->whenLoaded('upiPaymentRequest', fn () => $this->upiPaymentRequest ? new UpiPaymentRequestResource($this->upiPaymentRequest) : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
