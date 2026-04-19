<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertCurriculumRequest;
use App\Http\Resources\AcademicManagement\CurriculumResource;
use App\Models\AcademicManagement\Curriculum;
use App\Services\AcademicManagement\CurriculumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function __construct(protected CurriculumService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Curriculum::class);

        return CurriculumResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'school_class_id', 'subject_id', 'academic_term_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertCurriculumRequest $request): JsonResponse
    {
        $this->authorize('create', Curriculum::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Curriculum created successfully.', 'data' => new CurriculumResource($record)], 201);
    }

    public function show(Curriculum $curriculum): CurriculumResource
    {
        $this->authorize('view', $curriculum);

        return new CurriculumResource($this->service->show($curriculum->id));
    }

    public function update(UpsertCurriculumRequest $request, Curriculum $curriculum): JsonResponse
    {
        $this->authorize('update', $curriculum);
        $updated = $this->service->update($curriculum, $request->validated());

        return response()->json(['message' => 'Curriculum updated successfully.', 'data' => new CurriculumResource($updated)]);
    }

    public function destroy(Curriculum $curriculum): JsonResponse
    {
        $this->authorize('delete', $curriculum);
        $this->service->delete($curriculum);

        return response()->json(null, 204);
    }
}
