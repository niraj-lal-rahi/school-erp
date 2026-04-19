<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'admission_no' => $this->admission_no,
            'roll_no' => $this->roll_no,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'preferred_name' => $this->preferred_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'blood_group' => $this->blood_group,
            'photo_path' => $this->photo_path,
            'religion' => $this->religion,
            'current_status' => $this->current_status,
            'status' => $this->current_status,
            'admission_date' => optional($this->admission_date)->toDateString(),
            'joining_date' => optional($this->joining_date)->toDateString(),
            'notes' => $this->notes,
            'address' => $this->address,
            'medical_notes' => $this->medical_notes,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'code' => $this->category?->code,
            ]),
            'house' => $this->whenLoaded('house', fn () => [
                'id' => $this->house?->id,
                'name' => $this->house?->name,
                'code' => $this->house?->code,
                'color' => $this->house?->color,
            ]),
            'guardians' => GuardianResource::collection($this->whenLoaded('guardians')),
            'enrollments' => $this->whenLoaded('enrollments'),
            'admissions' => $this->whenLoaded('admissions'),
            'documents' => StudentDocumentResource::collection($this->whenLoaded('documents')),
            'medical_record' => $this->whenLoaded('latestMedicalRecord', fn () => $this->latestMedicalRecord ? new StudentMedicalRecordResource($this->latestMedicalRecord) : null),
            'status_history' => StudentStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'notes_entries' => StudentNoteResource::collection($this->whenLoaded('notesEntries')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
