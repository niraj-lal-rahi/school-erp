<?php

namespace App\Http\Requests\SIS;

use App\Models\StudentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('studentCategory');

        if ($category instanceof StudentCategory) {
            return $this->user()?->can('update', $category) ?? false;
        }

        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('studentCategory');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('student_categories', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($category?->id),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
