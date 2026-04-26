<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'template_id' => $this->template_id,
            'title' => $this->title,
            'message' => $this->message,
            'audience_type' => $this->audience_type,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'channel' => $this->channel,
            'scheduled_at' => optional($this->scheduled_at)->toAtomString(),
            'status' => $this->status,
            'processed_at' => optional($this->processed_at)->toAtomString(),
            'template' => $this->whenLoaded('template', fn () => $this->template ? [
                'id' => $this->template->id,
                'name' => $this->template->name,
                'code' => $this->template->code,
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
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
