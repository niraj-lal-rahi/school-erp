<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentMedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'blood_group' => $this->blood_group,
            'height' => $this->height,
            'weight' => $this->weight,
            'allergies' => $this->allergies,
            'medical_conditions' => $this->medical_conditions,
            'medications' => $this->medications,
            'doctor_name' => $this->doctor_name,
            'doctor_phone' => $this->doctor_phone,
            'hospital_name' => $this->hospital_name,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'insurance_provider' => $this->insurance_provider,
            'insurance_number' => $this->insurance_number,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
