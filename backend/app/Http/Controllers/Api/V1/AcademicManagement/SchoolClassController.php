<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertSchoolClassRequest;
use App\Http\Resources\AcademicManagement\SchoolClassResource;
use App\Models\SchoolClass;
use App\Services\AcademicManagement\SchoolClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolClassController extends Controller
{
    public function __construct(protected SchoolClassService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', SchoolClass::class);

        return SchoolClassResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertSchoolClassRequest $request): JsonResponse
    {
        $this->authorize('create', SchoolClass::class);
        $schoolClass = $this->service->create([...$request->validated(), 'uuid' => (string) Str::uuid(), 'sort_order' => $request->integer('level_order')]);

        return response()->json(['message' => 'Class created successfully.', 'data' => new SchoolClassResource($schoolClass)], 201);
    }

    public function show(SchoolClass $schoolClass): SchoolClassResource
    {
        $this->authorize('view', $schoolClass);

        return new SchoolClassResource($this->service->show($schoolClass->id));
    }

    public function update(UpsertSchoolClassRequest $request, SchoolClass $schoolClass): JsonResponse
    {
        $this->authorize('update', $schoolClass);
        $updated = $this->service->update($schoolClass, [...$request->validated(), 'sort_order' => $request->integer('level_order')]);

        return response()->json(['message' => 'Class updated successfully.', 'data' => new SchoolClassResource($updated)]);
    }

    public function destroy(SchoolClass $schoolClass): JsonResponse
    {
        $this->authorize('delete', $schoolClass);
        $this->service->delete($schoolClass);

        return response()->json(null, 204);
    }
}
