<?php

namespace App\Http\Controllers\Api\V1\Documents;

use App\Http\Requests\Documents\StoreDocumentCategoryRequest;
use App\Http\Requests\Documents\UpdateDocumentCategoryRequest;
use App\Http\Resources\Documents\DocumentCategoryResource;
use App\Models\Documents\DocumentCategory;
use App\Services\Documents\DocumentCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentCategoryController extends BaseDocumentController
{
    public function __construct(
        protected DocumentCategoryService $categories,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DocumentCategory::class);

        return response()->json([
            'data' => DocumentCategoryResource::collection($this->categories->paginate(
                $request->only(['search', 'applies_to', 'status']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreDocumentCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', DocumentCategory::class);
        $category = $this->categories->create($request->validated());

        return response()->json([
            'message' => 'Document category created successfully.',
            'data' => new DocumentCategoryResource($category),
        ], 201);
    }

    public function update(UpdateDocumentCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categories->findOrFail($id);
        $this->authorize('update', $category);
        $category = $this->categories->update($category, $request->validated());

        return response()->json([
            'message' => 'Document category updated successfully.',
            'data' => new DocumentCategoryResource($category),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->categories->findOrFail($id);
        $this->authorize('delete', $category);
        $this->categories->delete($category);

        return response()->json(null, 204);
    }
}
