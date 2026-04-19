<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\AdmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\ConvertAdmissionRequest;
use App\Http\Requests\SIS\ReviewAdmissionRequest;
use App\Http\Requests\SIS\UpsertAdmissionRequest;
use App\Http\Resources\SIS\AdmissionResource;
use App\Models\Admission;
use App\Services\SIS\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function __construct(
        protected AdmissionService $admissions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            AdmissionResource::collection($this->admissions->paginate(
                $request->only(['search', 'academic_year_id', 'class_id', 'section_id', 'application_status']),
                (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertAdmissionRequest $request): JsonResponse
    {
        $admission = $this->admissions->create(AdmissionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Admission application created successfully.',
            'data' => new AdmissionResource($admission),
        ], 201);
    }

    public function show(Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'data' => new AdmissionResource($this->admissions->show($studentAdmission)),
        ]);
    }

    public function update(UpsertAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        $admission = $this->admissions->update($studentAdmission, AdmissionData::fromArray([
            ...$studentAdmission->toArray(),
            ...$request->validated(),
        ]));

        return response()->json([
            'message' => 'Admission application updated successfully.',
            'data' => new AdmissionResource($admission),
        ]);
    }

    public function destroy(Admission $studentAdmission): JsonResponse
    {
        $this->admissions->delete($studentAdmission);

        return response()->json(null, 204);
    }

    public function submit(Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'message' => 'Admission submitted successfully.',
            'data' => new AdmissionResource($this->admissions->submit($studentAdmission)),
        ]);
    }

    public function review(ReviewAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'message' => 'Admission moved to review.',
            'data' => new AdmissionResource($this->admissions->review($studentAdmission, $request->user()->id, $request->validated('remarks'))),
        ]);
    }

    public function approve(ReviewAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'message' => 'Admission approved successfully.',
            'data' => new AdmissionResource($this->admissions->approve($studentAdmission, $request->user()->id, $request->validated('remarks'))),
        ]);
    }

    public function reject(ReviewAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'message' => 'Admission rejected successfully.',
            'data' => new AdmissionResource($this->admissions->reject($studentAdmission, $request->user()->id, $request->validated('remarks'))),
        ]);
    }

    public function waitlist(ReviewAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        return response()->json([
            'message' => 'Admission waitlisted successfully.',
            'data' => new AdmissionResource($this->admissions->waitlist($studentAdmission, $request->user()->id, $request->validated('remarks'))),
        ]);
    }

    public function convertToStudent(ConvertAdmissionRequest $request, Admission $studentAdmission): JsonResponse
    {
        $admission = $this->admissions->convertToStudent($studentAdmission, $request->validated(), $request->user()->id);

        return response()->json([
            'message' => 'Admission converted to student successfully.',
            'data' => new AdmissionResource($admission),
        ]);
    }
}
