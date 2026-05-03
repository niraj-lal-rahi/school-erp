<?php

namespace App\Http\Resources\Documents;

use App\Http\Resources\Concerns\SupportsIncludes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentListResource extends JsonResource
{
    use SupportsIncludes;

    public function toArray(Request $request): array
    {
        return $this->applySparseFieldset($request, [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'folder_id' => $this->folder_id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'title' => $this->title,
            'document_no' => $this->document_no,
            'issue_date' => optional($this->issue_date)?->toDateString(),
            'expiry_date' => optional($this->expiry_date)?->toDateString(),
            'verification_status' => $this->verification_status,
            'status' => $this->status,
            'is_expired' => $this->isExpired(),
            'is_verified' => $this->isVerified(),
            'category' => $this->includeRequested($request, 'category') && $this->relationLoaded('category')
                ? [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'code' => $this->category?->code,
                ]
                : null,
            'folder' => $this->includeRequested($request, 'folder') && $this->relationLoaded('folder')
                ? [
                    'id' => $this->folder?->id,
                    'name' => $this->folder?->name,
                    'code' => $this->folder?->code,
                    'visibility' => $this->folder?->visibility,
                ]
                : null,
            'current_file' => $this->includeRequested($request, 'currentFile') && $this->relationLoaded('currentFile')
                ? [
                    'id' => $this->currentFile?->id,
                    'version_no' => $this->currentFile?->version_no,
                    'file_name' => $this->currentFile?->file_name,
                    'original_file_name' => $this->currentFile?->original_file_name,
                    'mime_type' => $this->currentFile?->mime_type,
                    'file_size' => $this->currentFile?->file_size,
                ]
                : null,
            'tags' => $this->includeRequested($request, 'tags') && $this->relationLoaded('tags')
                ? $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'code' => $tag->code,
                ])->values()
                : null,
            'created_at' => optional($this->created_at)?->toAtomString(),
        ]);
    }
}
