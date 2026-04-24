<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\RecordStatus;
use App\Models\HR\Designation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $designation = $this->route('designation');

        if ($designation instanceof Designation) {
            return $this->user()?->can('update', $designation) ?? false;
        }

        return $this->user()?->can('create', Designation::class) ?? false;
    }

    public function rules(): array
    {
        $designation = $this->route('designation');
        $schoolId = $this->user()?->school_id;

        return [
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('hr_departments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('hr_designations', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($designation?->id),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(RecordStatus::values())],
        ];
    }
}
