<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\UpdateDocumentPermissionRequest;
use App\Http\Resources\Documents\DocumentPermissionResource;
use App\Services\Documents\DocumentPermissionService;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentPermissionController extends BaseDocumentController
{
    public function __construct(
        protected DocumentService $documents,
        protected DocumentPermissionService $permissions,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('managePermissions', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'update', $this->permissions);

        return response()->json([
            'data' => DocumentPermissionResource::collection($this->permissions->listByDocument($document)),
        ]);
    }

    public function store(UpdateDocumentPermissionRequest $request, int $id): JsonResponse
    {
        $document = $this->documents->findOrFail($id);
        $this->authorize('managePermissions', $document);
        $this->ensureDocumentAbility($request->user(), $document, 'update', $this->permissions);
        $permission = $this->permissions->grant($document, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Document permission added successfully.',
            'data' => new DocumentPermissionResource($permission),
        ], 201);
    }

    public function update(UpdateDocumentPermissionRequest $request, int $id): JsonResponse
    {
        $permission = $this->permissions->findOrFail($id);
        $this->authorize('managePermissions', $permission->document);
        $this->ensureDocumentAbility($request->user(), $permission->document, 'update', $this->permissions);
        $permission = $this->permissions->update($permission, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Document permission updated successfully.',
            'data' => new DocumentPermissionResource($permission),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $permission = $this->permissions->findOrFail($id);
        $this->authorize('managePermissions', $permission->document);
        $this->ensureDocumentAbility($request->user(), $permission->document, 'update', $this->permissions);
        $this->permissions->revoke($permission, $request->user());

        return response()->json(null, 204);
    }
}
