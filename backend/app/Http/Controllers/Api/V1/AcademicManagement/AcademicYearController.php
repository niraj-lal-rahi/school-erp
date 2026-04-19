<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpdateAcademicYearStatusRequest;
use App\Http\Requests\AcademicManagement\UpsertAcademicYearRequest;
use App\Http\Resources\AcademicManagement\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\AcademicManagement\AcademicYearService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademicYearController extends Controller
{
    public function __construct(protected AcademicYearService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AcademicYear::class);

        return AcademicYearResource::collection($this->service->paginate($request->only(['search', 'status', 'is_active']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertAcademicYearRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicYear::class);

        $academicYear = $this->service->create([
            ...$request->validated(),
            'uuid' => (string) Str::uuid(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Academic year created successfully.', 'data' => new AcademicYearResource($academicYear)], 201);
    }

    public function show(AcademicYear $academicYear): AcademicYearResource
    {
        $this->authorize('view', $academicYear);

        return new AcademicYearResource($this->service->show($academicYear->id));
    }

    public function update(UpsertAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('update', $academicYear);

        $updated = $this->service->update($academicYear, [...$request->validated(), 'updated_by' => $request->user()->id]);

        return response()->json(['message' => 'Academic year updated successfully.', 'data' => new AcademicYearResource($updated)]);
    }

    public function updateStatus(UpdateAcademicYearStatusRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('update', $academicYear);

        $updated = $this->service->updateStatus($academicYear, [...$request->validated(), 'updated_by' => $request->user()->id]);

        return response()->json(['message' => 'Academic year status updated successfully.', 'data' => new AcademicYearResource($updated)]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $this->authorize('delete', $academicYear);
        $this->service->delete($academicYear);

        return response()->json(null, 204);
    }
}
