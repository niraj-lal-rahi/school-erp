<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'title' => $this->title,
            'content' => $this->content,
            'announcement_type' => $this->announcement_type,
            'audience_type' => $this->audience_type,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'publish_at' => optional($this->publish_at)->toAtomString(),
            'expires_at' => optional($this->expires_at)->toAtomString(),
            'priority' => $this->priority,
            'status' => $this->status,
            'published_at' => optional($this->published_at)->toAtomString(),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
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
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher ? [
                'id' => $this->publisher->id,
                'name' => $this->publisher->name,
                'email' => $this->publisher->email,
            ] : null),
            'recipients_count' => $this->whenCounted('recipients'),
            'attachments_count' => $this->whenCounted('attachments'),
            'recipients' => AnnouncementRecipientResource::collection($this->whenLoaded('recipients')),
            'attachments' => MessageAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
