<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertSectionRequest;
use App\Http\Resources\AcademicManagement\SectionResource;
use App\Models\Section;
use App\Services\AcademicManagement\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function __construct(protected SectionService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Section::class);

        return SectionResource::collection($this->service->paginate($request->only(['search', 'school_class_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertSectionRequest $request): JsonResponse
    {
        $this->authorize('create', Section::class);
        $section = $this->service->create([...$request->validated(), 'uuid' => (string) Str::uuid()]);

        return response()->json(['message' => 'Section created successfully.', 'data' => new SectionResource($section)], 201);
    }

    public function show(Section $section): SectionResource
    {
        $this->authorize('view', $section);

        return new SectionResource($this->service->show($section->id));
    }

    public function update(UpsertSectionRequest $request, Section $section): JsonResponse
    {
        $this->authorize('update', $section);
        $updated = $this->service->update($section, $request->validated());

        return response()->json(['message' => 'Section updated successfully.', 'data' => new SectionResource($updated)]);
    }

    public function destroy(Section $section): JsonResponse
    {
        $this->authorize('delete', $section);
        $this->service->delete($section);

        return response()->json(null, 204);
    }
}
