<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentMedicalRecordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertStudentMedicalRecordRequest;
use App\Http\Resources\SIS\StudentMedicalRecordResource;
use App\Models\Student;
use App\Models\StudentMedicalRecord;
use App\Services\SIS\StudentMedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentMedicalRecordController extends Controller
{
    public function __construct(
        protected StudentMedicalRecordService $medicalRecords,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        return response()->json(
            StudentMedicalRecordResource::collection($this->medicalRecords->paginate(
                filters: $request->only(['search', 'student_id']),
                perPage: (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UpsertStudentMedicalRecordRequest $request): JsonResponse
    {
        $student = Student::query()->findOrFail((int) $request->integer('student_id'));
        $this->authorize('updateMedical', $student);

        $record = $this->medicalRecords->create(
            $student,
            StudentMedicalRecordData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student medical record created successfully.',
            'data' => new StudentMedicalRecordResource($record),
        ], 201);
    }

    public function show(StudentMedicalRecord $studentMedicalRecord): JsonResponse
    {
        $this->authorize('view', $studentMedicalRecord);

        return response()->json([
            'data' => new StudentMedicalRecordResource($this->medicalRecords->show($studentMedicalRecord)),
        ]);
    }

    public function update(UpsertStudentMedicalRecordRequest $request, StudentMedicalRecord $studentMedicalRecord): JsonResponse
    {
        $record = $this->medicalRecords->update(
            $studentMedicalRecord,
            StudentMedicalRecordData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student medical record updated successfully.',
            'data' => new StudentMedicalRecordResource($record),
        ]);
    }

    public function destroy(StudentMedicalRecord $studentMedicalRecord): JsonResponse
    {
        $this->authorize('delete', $studentMedicalRecord);
        $this->medicalRecords->delete($studentMedicalRecord);

        return response()->json(null, 204);
    }

    public function studentMedical(Student $student): JsonResponse
    {
        $this->authorize('viewMedical', $student);

        $record = $this->medicalRecords->latestForStudent($student);

        return response()->json([
            'data' => $record ? new StudentMedicalRecordResource($record) : null,
        ]);
    }

    public function upsertForStudent(UpsertStudentMedicalRecordRequest $request, Student $student): JsonResponse
    {
        $record = $this->medicalRecords->upsertForStudent(
            $student,
            StudentMedicalRecordData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student medical record saved successfully.',
            'data' => new StudentMedicalRecordResource($record),
        ]);
    }
}
