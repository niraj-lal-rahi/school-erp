<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\UploadDocumentVersionRequest;
use App\Http\Resources\Documents\DocumentFileResource;
use App\Services\Documents\DocumentPermissionService;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends BaseDocumentController
{
    public function __construct(
        protected DocumentService $documents,
        protected DocumentPermissionService $permissions,
    ) {
    }

    public function download(Request $request, int $id): StreamedResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('download', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'download', $this->permissions);

        return $this->documents->download($document, $request->user(), $request);
    }

    public function uploadVersion(UploadDocumentVersionRequest $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('update', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'update', $this->permissions);
        $documentFile = $this->documents->uploadVersion($document, $request->file('file'), $request->validated(), $request->user());

        return response()->json([
            'message' => 'Document version uploaded successfully.',
            'data' => new DocumentFileResource($documentFile),
        ], 201);
    }

    public function versions(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('view', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'view', $this->permissions);

        return response()->json([
            'data' => DocumentFileResource::collection($this->documents->versionHistory($document)),
        ]);
    }
}
