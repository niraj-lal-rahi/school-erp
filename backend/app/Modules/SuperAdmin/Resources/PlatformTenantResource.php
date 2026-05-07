<?php

namespace App\Modules\SuperAdmin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformTenantResource extends JsonResource
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
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'activated_at' => $this->activated_at?->toISOString(),
            'suspended_at' => $this->suspended_at?->toISOString(),
            'has_database_connection' => $this->relationLoaded('activeDatabaseConnection')
                ? $this->activeDatabaseConnection !== null
                : $this->activeDatabaseConnection()->exists(),
            'active_database_connection' => $this->when(
                $this->relationLoaded('activeDatabaseConnection') && $this->activeDatabaseConnection,
                fn () => new TenantDatabaseConnectionResource($this->activeDatabaseConnection)
            ),
            'security' => $this->when($this->relationLoaded('securitySetting') && $this->securitySetting, fn (): array => [
                'encryption_enabled' => (bool) $this->securitySetting->encryption_enabled,
                'database_isolated' => (bool) $this->securitySetting->database_isolated,
                'emergency_access_enabled' => (bool) $this->securitySetting->emergency_access_enabled,
                'backup_encryption_enabled' => (bool) $this->securitySetting->backup_encryption_enabled,
                'status' => $this->securitySetting->status,
            ]),
            'subscription' => $this->when($this->relationLoaded('subscriptions'), function (): ?array {
                $subscription = $this->subscriptions->sortByDesc('id')->first();

                if (! $subscription) {
                    return null;
                }

                return [
                    'id' => $subscription->id,
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'start_date' => optional($subscription->start_date)?->toDateString(),
                    'end_date' => optional($subscription->end_date)?->toDateString(),
                    'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
