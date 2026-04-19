<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentHouseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertStudentHouseRequest;
use App\Http\Resources\SIS\StudentHouseResource;
use App\Models\StudentHouse;
use App\Services\SIS\StudentHouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentHouseController extends Controller
{
    public function __construct(
        protected StudentHouseService $houses,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentHouse::class);

        return response()->json([
            'data' => StudentHouseResource::collection(
                $this->houses->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertStudentHouseRequest $request): JsonResponse
    {
        $house = $this->houses->create(
            StudentHouseData::fromArray([
                ...$request->validated(),
                'school_id' => $request->user()->school_id,
            ])
        );

        return response()->json([
            'message' => 'Student house created successfully.',
            'data' => new StudentHouseResource($house->loadCount('students')),
        ], 201);
    }

    public function update(UpsertStudentHouseRequest $request, StudentHouse $studentHouse): JsonResponse
    {
        $house = $this->houses->update($studentHouse, StudentHouseData::fromArray([
            ...$request->validated(),
            'school_id' => $studentHouse->school_id,
            'uuid' => $studentHouse->uuid,
        ]));

        return response()->json([
            'message' => 'Student house updated successfully.',
            'data' => new StudentHouseResource($house),
        ]);
    }

    public function destroy(StudentHouse $studentHouse): JsonResponse
    {
        $this->authorize('delete', $studentHouse);
        $this->houses->delete($studentHouse);

        return response()->json(null, 204);
    }
}
