<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'domain' => $this->domain,
            'subdomain' => $this->subdomain,
            'logo_path' => $this->logo_path,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'locale' => $this->locale,
            'status' => $this->status,
            'trial_ends_at' => optional($this->trial_ends_at)?->toAtomString(),
            'activated_at' => optional($this->activated_at)?->toAtomString(),
            'suspended_at' => optional($this->suspended_at)?->toAtomString(),
            'users_count' => $this->whenCounted('users'),
            'students_count' => $this->whenCounted('students'),
            'subscriptions_count' => $this->whenCounted('tenantSubscriptions'),
            'domains_count' => $this->whenCounted('domains'),
            'active_subscription' => $this->whenLoaded('activeSubscription', fn () => $this->activeSubscription ? new TenantSubscriptionResource($this->activeSubscription) : null),
            'usage' => $this->whenLoaded('usageLimit', fn () => $this->usageLimit ? new TenantUsageResource($this->usageLimit) : null),
            'domains' => TenantDomainResource::collection($this->whenLoaded('domains')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
