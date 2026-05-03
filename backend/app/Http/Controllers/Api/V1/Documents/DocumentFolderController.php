<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\StoreDocumentFolderRequest;
use App\Http\Requests\Documents\UpdateDocumentFolderRequest;
use App\Http\Resources\Documents\DocumentFolderResource;
use App\Models\Documents\DocumentFolder;
use App\Services\Documents\DocumentFolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentFolderController extends BaseDocumentController
{
    public function __construct(
        protected DocumentFolderService $folders,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DocumentFolder::class);

        return response()->json([
            'data' => DocumentFolderResource::collection($this->folders->paginate(
                $request->only(['search', 'parent_id', 'visibility']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreDocumentFolderRequest $request): JsonResponse
    {
        $this->authorize('create', DocumentFolder::class);
        $folder = $this->folders->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Document folder created successfully.',
            'data' => new DocumentFolderResource($folder),
        ], 201);
    }

    public function update(UpdateDocumentFolderRequest $request, int $id): JsonResponse
    {
        $folder = $this->folders->findOrFail($id);
        $this->authorize('update', $folder);
        $folder = $this->folders->update($folder, $request->validated());

        return response()->json([
            'message' => 'Document folder updated successfully.',
            'data' => new DocumentFolderResource($folder),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $folder = $this->folders->findOrFail($id);
        $this->authorize('delete', $folder);
        $this->folders->delete($folder);

        return response()->json(null, 204);
    }
}
