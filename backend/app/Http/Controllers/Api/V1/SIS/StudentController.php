<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentData;
use App\DataTransferObjects\SIS\StudentDocumentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\AssignStudentGuardianRequest;
use App\Http\Requests\SIS\StudentStatusActionRequest;
use App\Http\Requests\SIS\StoreStudentRequest;
use App\Http\Requests\SIS\UpdateStudentRequest;
use App\Http\Requests\SIS\UploadStudentDocumentRequest;
use App\Http\Resources\SIS\GuardianResource;
use App\Http\Resources\SIS\StudentListResource;
use App\Http\Resources\SIS\StudentStatusHistoryResource;
use App\Http\Resources\SIS\StudentResource;
use App\Models\Student;
use App\Services\SIS\StudentLifecycleService;
use App\Services\SIS\StudentService;
use App\Support\Api\ApiPaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $students,
        protected StudentLifecycleService $lifecycle,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        return response()->json(
            ApiPaginationHelper::fromResourceCollection(StudentListResource::collection($this->students->paginate(
                filters: $request->only([
                    'search',
                    'status',
                    'guardian_id',
                    'academic_year_id',
                    'school_class_id',
                    'section_id',
                    'category_id',
                    'house_id',
                ]),
                perPage: (int) $request->integer('per_page', 15),
            )))
        );
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->students->create(
            StudentData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return response()->json([
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student = $this->students->update(
            $student,
            StudentData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);
        $this->students->delete($student);

        return response()->json(null, 204);
    }

    public function assignGuardian(AssignStudentGuardianRequest $request, Student $student): JsonResponse
    {
        $student = $this->students->assignGuardian($student, $request->validated());

        return response()->json([
            'message' => 'Guardian assigned successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function removeGuardian(Student $student, int $guardianId): JsonResponse
    {
        $this->authorize('update', $student);
        $student = $this->students->removeGuardian($student, $guardianId);

        return response()->json([
            'message' => 'Guardian removed successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function guardians(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return response()->json([
            'data' => GuardianResource::collection($this->students->guardians($student)),
        ]);
    }

    public function uploadDocument(UploadStudentDocumentRequest $request, Student $student): JsonResponse
    {
        $document = $this->students->uploadDocument(
            student: $student,
            data: StudentDocumentData::fromArray($request->validated()),
            uploadedBy: $request->user()->id,
        );

        return response()->json([
            'message' => 'Student document uploaded successfully.',
            'data' => $document,
        ], 201);
    }

    public function statusHistory(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return response()->json([
            'data' => StudentStatusHistoryResource::collection($this->lifecycle->statusHistory($student)),
        ]);
    }

    public function promote(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->promote($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student promoted successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function transferSection(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->transferSection($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student section transferred successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function withdraw(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->withdraw($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student withdrawn successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function graduate(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->graduate($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student marked as graduated successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function suspend(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->suspend($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student suspended successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }

    public function reactivate(StudentStatusActionRequest $request, Student $student): JsonResponse
    {
        $student = $this->lifecycle->reactivate($student, \App\DataTransferObjects\SIS\StudentStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Student reactivated successfully.',
            'data' => new StudentResource($this->students->show($student)),
        ]);
    }
}
