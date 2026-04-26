<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'group_type' => $this->group_type,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'description' => $this->description,
            'status' => $this->status,
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
                'code' => $this->schoolClass->code,
            ] : null),
            'section' => $this->whenLoaded('section', fn () => $this->section ? [
                'id' => $this->section->id,
                'name' => $this->section->name,
                'code' => $this->section->code,
            ] : null),
            'members_count' => $this->whenCounted('members'),
            'members' => CommunicationGroupMemberResource::collection($this->whenLoaded('members')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
