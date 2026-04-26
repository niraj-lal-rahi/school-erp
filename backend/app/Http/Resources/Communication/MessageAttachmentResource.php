<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->message_id,
            'announcement_id' => $this->announcement_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
