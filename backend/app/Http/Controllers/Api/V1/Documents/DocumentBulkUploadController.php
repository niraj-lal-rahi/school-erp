<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\BulkUploadDocumentRequest;
use App\Http\Resources\Documents\DocumentBulkUploadResource;
use App\Models\Documents\Document;
use App\Services\Documents\DocumentBulkUploadService;
use Illuminate\Http\JsonResponse;

class DocumentBulkUploadController extends BaseDocumentController
{
    public function __construct(
        protected DocumentBulkUploadService $bulkUploads,
    ) {
    }

    public function store(BulkUploadDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', [Document::class, $request->validated()]);
        $bulkUpload = $this->bulkUploads->queueUpload(
            $request->validated(),
            $request->file('file'),
            $request->user(),
        );

        return response()->json([
            'message' => 'Bulk upload queued successfully.',
            'data' => new DocumentBulkUploadResource($bulkUpload),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => new DocumentBulkUploadResource($this->bulkUploads->findOrFail($id)),
        ]);
    }
}
