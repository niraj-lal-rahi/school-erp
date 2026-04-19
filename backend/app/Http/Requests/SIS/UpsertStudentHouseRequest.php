<?php

namespace App\Http\Requests\SIS;

use App\Models\StudentHouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $house = $this->route('studentHouse');

        if ($house instanceof StudentHouse) {
            return $this->user()?->can('update', $house) ?? false;
        }

        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        $house = $this->route('studentHouse');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('student_houses', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($house?->id),
            ],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
