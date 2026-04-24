<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
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
            'staff' => $this->whenLoaded('staff', fn () => [
                'id' => $this->staff?->id,
                'employee_code' => $this->staff?->employee_code,
                'full_name' => $this->staff?->full_name,
            ]),
            'uploaded_by' => $this->whenLoaded('uploader', fn () => $this->uploader ? [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
