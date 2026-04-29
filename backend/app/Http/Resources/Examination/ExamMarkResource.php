<?php

namespace App\Http\Resources\Examination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamMarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'subject_id' => $this->subject_id,
            'marks_obtained' => $this->marks_obtained,
            'is_absent' => (bool) $this->is_absent,
            'remarks' => $this->remarks,
            'evaluated_at' => optional($this->evaluated_at)->toAtomString(),
            'exam' => $this->whenLoaded('exam', fn () => $this->exam ? [
                'id' => $this->exam->id,
                'name' => $this->exam->name,
                'code' => $this->exam->code,
                'result_status' => $this->exam->result_status,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
                'roll_no' => $this->student->roll_no,
            ] : null),
            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
                'code' => $this->subject->code,
            ] : null),
            'evaluator' => $this->whenLoaded('evaluator', fn () => $this->evaluator ? [
                'id' => $this->evaluator->id,
                'name' => $this->evaluator->name,
                'email' => $this->evaluator->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
