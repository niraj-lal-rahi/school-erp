<?php

namespace App\Http\Resources\Documents;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'category_id' => $this->category_id,
            'folder_id' => $this->folder_id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'title' => $this->title,
            'description' => $this->description,
            'document_no' => $this->document_no,
            'issue_date' => optional($this->issue_date)?->toDateString(),
            'expiry_date' => optional($this->expiry_date)?->toDateString(),
            'verification_status' => $this->verification_status,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'is_expired' => $this->isExpired(),
            'is_verified' => $this->isVerified(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? new DocumentCategoryResource($this->category) : null),
            'folder' => $this->whenLoaded('folder', fn () => $this->folder ? new DocumentFolderResource($this->folder) : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'current_file' => $this->whenLoaded('currentFile', fn () => $this->currentFile ? new DocumentFileResource($this->currentFile) : null),
            'files' => DocumentFileResource::collection($this->whenLoaded('files')),
            'permissions' => DocumentPermissionResource::collection($this->whenLoaded('permissions')),
            'verifications' => DocumentVerificationResource::collection($this->whenLoaded('verifications')),
            'tags' => DocumentTagResource::collection($this->whenLoaded('tags')),
            'audit_logs' => DocumentAuditLogResource::collection($this->whenLoaded('auditLogs')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
