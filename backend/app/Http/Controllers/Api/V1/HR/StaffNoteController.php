<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffNoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertStaffNoteRequest;
use App\Http\Resources\HR\StaffNoteResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffNote;
use App\Services\HR\StaffNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffNoteController extends Controller
{
    public function __construct(protected StaffNoteService $notes)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json([
            'data' => StaffNoteResource::collection($this->notes->all($request->only(['staff_id']))),
        ]);
    }

    public function store(UpsertStaffNoteRequest $request, Staff $staff): JsonResponse
    {
        $note = $this->notes->create($staff, StaffNoteData::fromArray([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Staff note created successfully.',
            'data' => new StaffNoteResource($note->load('creator')),
        ], 201);
    }

    public function update(UpsertStaffNoteRequest $request, StaffNote $staffNote): JsonResponse
    {
        $note = $this->notes->update($staffNote, StaffNoteData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Staff note updated successfully.',
            'data' => new StaffNoteResource($note),
        ]);
    }

    public function destroy(StaffNote $staffNote): JsonResponse
    {
        $this->authorize('update', $staffNote->staff);
        $this->notes->delete($staffNote);

        return response()->json(null, 204);
    }

    public function staffNotes(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffNoteResource::collection($this->notes->allForStaff($staff)),
        ]);
    }
}
