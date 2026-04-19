<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertLessonPlanRequest;
use App\Http\Resources\AcademicManagement\LessonPlanResource;
use App\Models\AcademicManagement\LessonPlan;
use App\Services\AcademicManagement\LessonPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    public function __construct(protected LessonPlanService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', LessonPlan::class);

        return LessonPlanResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertLessonPlanRequest $request): JsonResponse
    {
        $this->authorize('create', LessonPlan::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Lesson plan created successfully.', 'data' => new LessonPlanResource($record)], 201);
    }

    public function show(LessonPlan $lessonPlan): LessonPlanResource
    {
        $this->authorize('view', $lessonPlan);

        return new LessonPlanResource($this->service->show($lessonPlan->id));
    }

    public function update(UpsertLessonPlanRequest $request, LessonPlan $lessonPlan): JsonResponse
    {
        $this->authorize('update', $lessonPlan);
        $updated = $this->service->update($lessonPlan, $request->validated());

        return response()->json(['message' => 'Lesson plan updated successfully.', 'data' => new LessonPlanResource($updated)]);
    }

    public function destroy(LessonPlan $lessonPlan): JsonResponse
    {
        $this->authorize('delete', $lessonPlan);
        $this->service->delete($lessonPlan);

        return response()->json(null, 204);
    }
}
