<?php

namespace App\Http\Controllers\Api\V1\SIS;

use App\DataTransferObjects\SIS\StudentNoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertStudentNoteRequest;
use App\Http\Resources\SIS\StudentNoteResource;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\SIS\StudentNoteService;
use Illuminate\Http\JsonResponse;

class StudentNoteController extends Controller
{
    public function __construct(
        protected StudentNoteService $notes,
    ) {
    }

    public function studentNotes(Student $student): JsonResponse
    {
        $this->authorize('manageNotes', $student);

        return response()->json([
            'data' => StudentNoteResource::collection($this->notes->allForStudent($student)),
        ]);
    }

    public function store(UpsertStudentNoteRequest $request, Student $student): JsonResponse
    {
        $note = $this->notes->create(
            $student,
            StudentNoteData::fromArray($request->validated()),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Student note saved successfully.',
            'data' => new StudentNoteResource($note),
        ], 201);
    }

    public function update(UpsertStudentNoteRequest $request, StudentNote $studentNote): JsonResponse
    {
        $note = $this->notes->update($studentNote, StudentNoteData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Student note updated successfully.',
            'data' => new StudentNoteResource($note),
        ]);
    }

    public function destroy(StudentNote $studentNote): JsonResponse
    {
        $this->authorize('delete', $studentNote);
        $this->notes->delete($studentNote);

        return response()->json(null, 204);
    }
}
