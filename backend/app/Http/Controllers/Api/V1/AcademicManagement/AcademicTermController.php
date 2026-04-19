<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertAcademicTermRequest;
use App\Http\Resources\AcademicManagement\AcademicTermResource;
use App\Models\AcademicManagement\AcademicTerm;
use App\Services\AcademicManagement\AcademicTermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademicTermController extends Controller
{
    public function __construct(protected AcademicTermService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AcademicTerm::class);

        return AcademicTermResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertAcademicTermRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicTerm::class);
        $term = $this->service->create([...$request->validated(), 'uuid' => (string) Str::uuid()]);

        return response()->json(['message' => 'Term created successfully.', 'data' => new AcademicTermResource($term)], 201);
    }

    public function show(AcademicTerm $term): AcademicTermResource
    {
        $this->authorize('view', $term);

        return new AcademicTermResource($this->service->show($term->id));
    }

    public function update(UpsertAcademicTermRequest $request, AcademicTerm $term): JsonResponse
    {
        $this->authorize('update', $term);
        $updated = $this->service->update($term, $request->validated());

        return response()->json(['message' => 'Term updated successfully.', 'data' => new AcademicTermResource($updated)]);
    }

    public function destroy(AcademicTerm $term): JsonResponse
    {
        $this->authorize('delete', $term);
        $this->service->delete($term);

        return response()->json(null, 204);
    }
}
