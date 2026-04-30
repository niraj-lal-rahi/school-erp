<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return [
                'id' => $this->resource['id'] ?? null,
                'profile_type' => $this->resource['profile_type'] ?? null,
                'profile_id' => $this->resource['profile_id'] ?? null,
                'is_default' => (bool) ($this->resource['is_default'] ?? false),
                'status' => $this->resource['status'] ?? null,
                'profile' => $this->resource['profile'] ?? null,
            ];
        }

        return [
            'id' => $this->id,
            'profile_type' => $this->profile_type,
            'profile_id' => $this->profile_id,
            'is_default' => (bool) $this->is_default,
            'status' => $this->status,
            'profile' => match ($this->profile_type) {
                'student' => $this->studentProfile ? [
                    'id' => $this->studentProfile->id,
                    'full_name' => $this->studentProfile->full_name,
                    'admission_no' => $this->studentProfile->admission_no,
                    'roll_no' => $this->studentProfile->roll_no,
                    'email' => $this->studentProfile->email,
                    'phone' => $this->studentProfile->phone,
                ] : null,
                'guardian' => $this->guardianProfile ? [
                    'id' => $this->guardianProfile->id,
                    'full_name' => $this->guardianProfile->full_name,
                    'relationship_type' => $this->guardianProfile->relationship_type,
                    'email' => $this->guardianProfile->email,
                    'phone' => $this->guardianProfile->phone,
                ] : null,
                default => null,
            },
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
