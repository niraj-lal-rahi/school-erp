<?php

namespace App\Http\Resources\Examination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'code' => $this->code,
            'exam_type_id' => $this->exam_type_id,
            'term_id' => $this->term_id,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id,
            'start_date' => optional($this->start_date)->toDateString(),
            'end_date' => optional($this->end_date)->toDateString(),
            'total_marks' => $this->total_marks,
            'passing_marks' => $this->passing_marks,
            'result_status' => $this->result_status,
            'subjects_count' => $this->whenCounted('examSubjects'),
            'enrollments_count' => $this->whenCounted('studentExamEnrollments'),
            'marks_count' => $this->whenCounted('examMarks'),
            'results_count' => $this->whenCounted('studentResults'),
            'exam_type' => $this->whenLoaded('examType', fn () => $this->examType ? [
                'id' => $this->examType->id,
                'name' => $this->examType->name,
                'code' => $this->examType->code,
                'status' => $this->examType->status,
            ] : null),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
            'term' => $this->whenLoaded('term', fn () => $this->term ? [
                'id' => $this->term->id,
                'name' => $this->term->name,
                'code' => $this->term->code,
            ] : null),
            'class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
                'code' => $this->schoolClass->code,
            ] : null),
            'section' => $this->whenLoaded('section', fn () => $this->section ? [
                'id' => $this->section->id,
                'name' => $this->section->name,
                'code' => $this->section->code,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'subjects' => ExamSubjectResource::collection($this->whenLoaded('examSubjects')),
            'publication' => $this->whenLoaded('resultPublication', fn () => $this->resultPublication ? [
                'id' => $this->resultPublication->id,
                'published_at' => optional($this->resultPublication->published_at)->toAtomString(),
                'is_public' => (bool) $this->resultPublication->is_public,
                'notify_users' => (bool) $this->resultPublication->notify_users,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
