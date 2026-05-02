<?php

namespace App\Http\Resources\Saas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantDomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'domain' => $this->domain,
            'domain_type' => $this->domain_type,
            'is_verified' => (bool) $this->is_verified,
            'verified_at' => optional($this->verified_at)?->toAtomString(),
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
