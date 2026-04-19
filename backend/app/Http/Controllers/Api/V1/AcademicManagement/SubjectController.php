<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertSubjectRequest;
use App\Http\Resources\AcademicManagement\SubjectResource;
use App\Models\AcademicManagement\Subject;
use App\Services\AcademicManagement\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function __construct(protected SubjectService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Subject::class);

        return SubjectResource::collection($this->service->paginate($request->only(['search', 'type', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertSubjectRequest $request): JsonResponse
    {
        $this->authorize('create', Subject::class);
        $subject = $this->service->create([...$request->validated(), 'uuid' => (string) Str::uuid()]);

        return response()->json(['message' => 'Subject created successfully.', 'data' => new SubjectResource($subject)], 201);
    }

    public function show(Subject $subject): SubjectResource
    {
        $this->authorize('view', $subject);

        return new SubjectResource($this->service->show($subject->id));
    }

    public function update(UpsertSubjectRequest $request, Subject $subject): JsonResponse
    {
        $this->authorize('update', $subject);
        $updated = $this->service->update($subject, $request->validated());

        return response()->json(['message' => 'Subject updated successfully.', 'data' => new SubjectResource($updated)]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $this->authorize('delete', $subject);
        $this->service->delete($subject);

        return response()->json(null, 204);
    }
}
