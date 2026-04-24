<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffEmergencyContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertStaffEmergencyContactRequest;
use App\Http\Resources\HR\StaffEmergencyContactResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffEmergencyContact;
use App\Services\HR\StaffEmergencyContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffEmergencyContactController extends Controller
{
    public function __construct(
        protected StaffEmergencyContactService $contacts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json([
            'data' => StaffEmergencyContactResource::collection(
                $this->contacts->all($request->only(['staff_id']))
            ),
        ]);
    }

    public function store(UpsertStaffEmergencyContactRequest $request, Staff $staff): JsonResponse
    {
        $contact = $this->contacts->create($staff, StaffEmergencyContactData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Emergency contact created successfully.',
            'data' => new StaffEmergencyContactResource($contact),
        ], 201);
    }

    public function update(UpsertStaffEmergencyContactRequest $request, StaffEmergencyContact $staffEmergencyContact): JsonResponse
    {
        $contact = $this->contacts->update($staffEmergencyContact, StaffEmergencyContactData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Emergency contact updated successfully.',
            'data' => new StaffEmergencyContactResource($contact),
        ]);
    }

    public function destroy(StaffEmergencyContact $staffEmergencyContact): JsonResponse
    {
        $this->authorize('update', $staffEmergencyContact->staff);
        $this->contacts->delete($staffEmergencyContact);

        return response()->json(null, 204);
    }

    public function staffContacts(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffEmergencyContactResource::collection($this->contacts->allForStaff($staff)),
        ]);
    }
}
