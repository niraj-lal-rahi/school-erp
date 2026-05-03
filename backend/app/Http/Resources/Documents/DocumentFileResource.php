<?php

namespace App\Http\Resources\Documents;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'version_no' => $this->version_no,
            'file_name' => $this->file_name,
            'original_file_name' => $this->original_file_name,
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'checksum' => $this->checksum,
            'uploaded_by' => $this->uploaded_by,
            'is_current' => (bool) $this->is_current,
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader ? [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
                'email' => $this->uploader->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
