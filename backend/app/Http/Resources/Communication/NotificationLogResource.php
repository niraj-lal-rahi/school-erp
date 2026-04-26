<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $notifiable = $this->resolveNotifiable();

        return [
            'id' => $this->id,
            'notifiable_type' => $this->notifiable_type,
            'notifiable_id' => $this->notifiable_id,
            'channel' => $this->channel,
            'template_id' => $this->template_id,
            'subject' => $this->subject,
            'message' => $this->message,
            'provider' => $this->provider,
            'provider_message_id' => $this->provider_message_id,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'sent_at' => optional($this->sent_at)->toAtomString(),
            'delivered_at' => optional($this->delivered_at)->toAtomString(),
            'read_at' => optional($this->read_at)->toAtomString(),
            'template' => $this->whenLoaded('template', fn () => $this->template ? [
                'id' => $this->template->id,
                'name' => $this->template->name,
                'code' => $this->template->code,
                'template_type' => $this->template->template_type,
            ] : null),
            'notifiable' => $notifiable ? [
                'id' => $notifiable->id,
                'name' => $notifiable->full_name ?? $notifiable->name ?? trim(($notifiable->first_name ?? '').' '.($notifiable->last_name ?? '')),
                'email' => $notifiable->email ?? null,
                'phone' => $notifiable->phone ?? null,
            ] : null,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
