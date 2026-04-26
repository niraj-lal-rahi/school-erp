<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'template_type' => $this->template_type,
            'subject' => $this->subject,
            'body' => $this->body,
            'variables' => $this->variables,
            'status' => $this->status,
            'notification_logs_count' => $this->whenCounted('notificationLogs'),
            'scheduled_messages_count' => $this->whenCounted('scheduledMessages'),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
