<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'photo_path' => $this->photo_path,
            'staff_type' => $this->staff_type,
            'employment_type' => $this->employment_type,
            'joining_date' => optional($this->joining_date)->toDateString(),
            'leaving_date' => optional($this->leaving_date)->toDateString(),
            'current_status' => $this->current_status,
            'qualification_summary' => $this->qualification_summary,
            'experience_years' => $this->experience_years,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'notes' => $this->notes,
            'department' => $this->whenLoaded('department', fn () => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ] : null),
            'designation' => $this->whenLoaded('designation', fn () => $this->designation ? [
                'id' => $this->designation->id,
                'name' => $this->designation->name,
                'code' => $this->designation->code,
            ] : null),
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null),
            'documents' => StaffDocumentResource::collection($this->whenLoaded('documents')),
            'emergency_contacts' => StaffEmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),
            'qualifications' => StaffQualificationResource::collection($this->whenLoaded('qualifications')),
            'work_experiences' => StaffWorkExperienceResource::collection($this->whenLoaded('workExperiences')),
            'attendance_records' => StaffAttendanceResource::collection($this->whenLoaded('attendanceRecords')),
            'bank_details' => StaffBankDetailResource::collection($this->whenLoaded('bankDetails')),
            'status_history' => StaffStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'notes_entries' => StaffNoteResource::collection($this->whenLoaded('notesEntries')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
