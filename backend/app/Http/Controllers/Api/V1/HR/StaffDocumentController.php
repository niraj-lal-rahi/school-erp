<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffDocumentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpdateStaffDocumentRequest;
use App\Http\Requests\HR\UploadStaffDocumentRequest;
use App\Http\Resources\HR\StaffDocumentResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffDocument;
use App\Services\HR\StaffDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffDocumentController extends Controller
{
    public function __construct(
        protected StaffDocumentService $documents,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json(
            StaffDocumentResource::collection($this->documents->paginate(
                filters: $request->only(['search', 'staff_id', 'document_type', 'verification_status']),
                perPage: (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UploadStaffDocumentRequest $request, Staff $staff): JsonResponse
    {
        $document = $this->documents->upload($staff, StaffDocumentData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff document uploaded successfully.',
            'data' => new StaffDocumentResource($document),
        ], 201);
    }

    public function show(StaffDocument $staffDocument): JsonResponse
    {
        $this->authorize('view', $staffDocument->staff);

        return response()->json([
            'data' => new StaffDocumentResource($this->documents->show($staffDocument)),
        ]);
    }

    public function update(UpdateStaffDocumentRequest $request, StaffDocument $staffDocument): JsonResponse
    {
        $document = $this->documents->update($staffDocument, $request->validated());

        return response()->json([
            'message' => 'Staff document updated successfully.',
            'data' => new StaffDocumentResource($document),
        ]);
    }

    public function destroy(StaffDocument $staffDocument): JsonResponse
    {
        $this->authorize('update', $staffDocument->staff);
        $this->documents->delete($staffDocument);

        return response()->json(null, 204);
    }

    public function staffDocuments(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffDocumentResource::collection($this->documents->allForStaff($staff)),
        ]);
    }
}
