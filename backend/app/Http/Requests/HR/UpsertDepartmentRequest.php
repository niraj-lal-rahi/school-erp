<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\RecordStatus;
use App\Models\HR\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $department = $this->route('department');

        if ($department instanceof Department) {
            return $this->user()?->can('update', $department) ?? false;
        }

        return $this->user()?->can('create', Department::class) ?? false;
    }

    public function rules(): array
    {
        $department = $this->route('department');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('hr_departments', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($department?->id),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(RecordStatus::values())],
        ];
    }
}
