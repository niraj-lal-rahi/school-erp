<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'document_type' => $this->document_type,
            'title' => $this->title,
            'disk' => $this->disk,
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'issued_by' => $this->issued_by,
            'issued_date' => optional($this->issued_date)->toDateString(),
            'expiry_date' => optional($this->expiry_date)->toDateString(),
            'verification_status' => $this->verification_status,
            'remarks' => $this->remarks,
            'metadata' => $this->metadata,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_by_name' => $this->uploader?->name,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
