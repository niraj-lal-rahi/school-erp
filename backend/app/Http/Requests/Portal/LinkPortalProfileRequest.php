<?php

namespace App\Http\Requests\Portal;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LinkPortalProfileRequest extends PortalRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', $this->existsInTenant('users')],
            'profile_type' => ['required', Rule::in(['student', 'guardian'])],
            'student_id' => ['nullable', 'integer', $this->existsInTenant('students')],
            'guardian_id' => ['nullable', 'integer', $this->existsInTenant('guardians')],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $profileType = $this->input('profile_type');
            $studentId = $this->integer('student_id');
            $guardianId = $this->integer('guardian_id');

            if ($profileType === 'student' && ! $studentId) {
                $validator->errors()->add('student_id', 'Student profile linking requires a student.');
            }

            if ($profileType === 'guardian' && ! $guardianId) {
                $validator->errors()->add('guardian_id', 'Guardian profile linking requires a guardian.');
            }

            if ($profileType === 'student' && $guardianId) {
                $validator->errors()->add('guardian_id', 'Guardian ID is not allowed for a student profile.');
            }

            if ($profileType === 'guardian' && $studentId) {
                $validator->errors()->add('student_id', 'Student ID is not allowed for a guardian profile link.');
            }

            if ($profileType === 'student' && $studentId && ! $this->userOwnsStudent($studentId) && $this->user()?->id === $this->integer('user_id')) {
                $validator->errors()->add('student_id', 'A student user can only link to their own student record.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator);
    }
}
