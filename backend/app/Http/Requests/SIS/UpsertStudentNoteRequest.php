<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');
        $note = $this->route('studentNote');

        if ($student instanceof Student) {
            return $this->user()?->can('manageNotes', $student) ?? false;
        }

        if ($note instanceof StudentNote) {
            return $this->user()?->can('update', $note) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
            'visibility_type' => ['nullable', 'string', Rule::in(['internal', 'private', 'admin_only'])],
        ];
    }
}
