<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationGroupMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $member = $this->resolveMember();

        return [
            'id' => $this->id,
            'member_type' => $this->member_type,
            'member_id' => $this->member_id,
            'member' => $member ? [
                'id' => $member->id,
                'name' => $member->full_name ?? $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                'email' => $member->email ?? null,
                'phone' => $member->phone ?? null,
            ] : null,
            'joined_at' => optional($this->joined_at)->toAtomString(),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
