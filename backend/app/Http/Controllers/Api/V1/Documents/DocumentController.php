<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Http\Requests\Documents\UploadDocumentRequest;
use App\Http\Resources\Documents\DocumentAuditLogResource;
use App\Http\Resources\Documents\DocumentResource;
use App\Models\Documents\Document;
use App\Services\Documents\DocumentExpiryService;
use App\Services\Documents\DocumentPermissionService;
use App\Services\Documents\DocumentService;
use App\Services\Documents\DocumentStorageUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends BaseDocumentController
{
    public function __construct(
        protected DocumentService $documents,
        protected DocumentPermissionService $permissions,
        protected DocumentExpiryService $expiry,
        protected DocumentStorageUsageService $storageUsage,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => DocumentResource::collection($this->documents->paginate(
                $request->only([
                    'search',
                    'owner_type',
                    'owner_id',
                    'category_id',
                    'folder_id',
                    'verification_status',
                    'expiry_date_from',
                    'expiry_date_to',
                    'status',
                    'tags',
                ]),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(UploadDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', [Document::class, $request->validated()]);
        $document = $this->documents->createWithUpload(
            $request->validated(),
            $request->file('file'),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'data' => new DocumentResource($document),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('view', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'view', $this->permissions);
        $this->documents->recordView($document, $request->user(), $request);

        return response()->json([
            'data' => new DocumentResource($document),
        ]);
    }

    public function update(UpdateDocumentRequest $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('update', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'update', $this->permissions);
        $document = $this->documents->update($document, $request->validated(), $request->user(), $request);

        return response()->json([
            'message' => 'Document updated successfully.',
            'data' => new DocumentResource($document),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('delete', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'delete', $this->permissions);
        $this->documents->delete($document, $request->user(), $request);

        return response()->json(null, 204);
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('restore', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'update', $this->permissions);
        $document = $this->documents->restore($document, $request->user(), $request);

        return response()->json([
            'message' => 'Document restored successfully.',
            'data' => new DocumentResource($document),
        ]);
    }

    public function auditLogs(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('view', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'view', $this->permissions);

        return response()->json([
            'data' => DocumentAuditLogResource::collection($this->documents->auditLogs($document)),
        ]);
    }

    public function expiringReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => DocumentResource::collection($this->expiry->expiringWithin((int) $request->integer('days', 30))),
        ]);
    }

    public function expiredReport(): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => DocumentResource::collection($this->expiry->expired()),
        ]);
    }

    public function verificationStatusReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => $this->documents->verificationStatusSummary($request->user()?->school_id),
        ]);
    }

    public function storageUsageReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Document::class);

        return response()->json([
            'data' => $this->storageUsage->usage($request->user()?->school_id),
        ]);
    }
}
