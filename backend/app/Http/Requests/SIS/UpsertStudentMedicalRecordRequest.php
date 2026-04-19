<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use App\Models\StudentMedicalRecord;
use Illuminate\Foundation\Http\FormRequest;

class UpsertStudentMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');
        $record = $this->route('studentMedicalRecord');

        if ($student instanceof Student) {
            return $this->user()?->can('updateMedical', $student) ?? false;
        }

        if ($record instanceof StudentMedicalRecord) {
            return $this->user()?->can('update', $record) ?? false;
        }

        return $this->user()?->hasPermission('students.medical.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'integer', 'exists:students,id'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'doctor_name' => ['nullable', 'string', 'max:255'],
            'doctor_phone' => ['nullable', 'string', 'max:30'],
            'hospital_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
