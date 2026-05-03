<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\StoreDocumentTagRequest;
use App\Http\Resources\Documents\DocumentTagResource;
use App\Models\Documents\DocumentCategory;
use App\Services\Documents\DocumentTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTagController extends BaseDocumentController
{
    public function __construct(
        protected DocumentTagService $tags,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DocumentCategory::class);

        return response()->json([
            'data' => DocumentTagResource::collection($this->tags->paginate(
                $request->only(['search']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreDocumentTagRequest $request): JsonResponse
    {
        $this->authorize('create', DocumentCategory::class);
        $tag = $this->tags->create($request->validated());

        return response()->json([
            'message' => 'Document tag created successfully.',
            'data' => new DocumentTagResource($tag),
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $tag = $this->tags->findOrFail($id);
        $this->authorize('create', DocumentCategory::class);
        $this->tags->delete($tag);

        return response()->json(null, 204);
    }
}
