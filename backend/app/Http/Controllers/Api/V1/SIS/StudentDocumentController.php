<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpdateStudentDocumentRequest;
use App\Http\Requests\SIS\UploadStudentDocumentRequest;
use App\Http\Resources\SIS\StudentDocumentResource;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\SIS\StudentDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    public function __construct(
        protected StudentDocumentService $documents,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        return response()->json(
            StudentDocumentResource::collection($this->documents->paginate(
                filters: $request->only(['search', 'student_id', 'document_type', 'verification_status']),
                perPage: (int) $request->integer('per_page', 15),
            ))->response()->getData(true)
        );
    }

    public function store(UploadStudentDocumentRequest $request, Student $student): JsonResponse
    {
        $document = $this->documents->upload(
            $student,
            StudentDocumentData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student document uploaded successfully.',
            'data' => new StudentDocumentResource($document),
        ], 201);
    }

    public function show(StudentDocument $studentDocument): JsonResponse
    {
        $this->authorize('view', $studentDocument);

        return response()->json([
            'data' => new StudentDocumentResource($this->documents->show($studentDocument)),
        ]);
    }

    public function update(UpdateStudentDocumentRequest $request, StudentDocument $studentDocument): JsonResponse
    {
        $document = $this->documents->update($studentDocument, $request->validated());

        return response()->json([
            'message' => 'Student document updated successfully.',
            'data' => new StudentDocumentResource($document),
        ]);
    }

    public function destroy(StudentDocument $studentDocument): JsonResponse
    {
        $this->authorize('delete', $studentDocument);
        $this->documents->delete($studentDocument);

        return response()->json(null, 204);
    }

    public function studentDocuments(Student $student): JsonResponse
    {
        $this->authorize('viewDocuments', $student);

        return response()->json([
            'data' => StudentDocumentResource::collection($this->documents->allForStudent($student)),
        ]);
    }
}
