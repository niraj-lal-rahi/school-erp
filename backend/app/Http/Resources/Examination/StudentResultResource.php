<?php

namespace App\Http\Resources\Examination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'total_marks' => $this->total_marks,
            'obtained_marks' => $this->obtained_marks,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'gpa' => $this->gpa,
            'result_status' => $this->result_status,
            'rank' => $this->rank,
            'remarks' => $this->remarks,
            'computed_at' => optional($this->computed_at)->toAtomString(),
            'exam' => $this->whenLoaded('exam', fn () => $this->exam ? [
                'id' => $this->exam->id,
                'name' => $this->exam->name,
                'code' => $this->exam->code,
                'class_id' => $this->exam->class_id,
                'section_id' => $this->exam->section_id,
                'result_status' => $this->exam->result_status,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
                'roll_no' => $this->student->roll_no,
            ] : null),
            'subject_details' => ResultSubjectDetailResource::collection($this->whenLoaded('resultSubjectDetails')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
