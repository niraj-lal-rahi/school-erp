<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\StaffNoteVisibility;
use App\Models\HR\Staff;
use App\Models\HR\StaffNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStaffNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        $note = $this->route('staffNote');

        if ($staff instanceof Staff) {
            return $this->user()?->can('update', $staff) ?? false;
        }

        if ($note instanceof StaffNote) {
            return $this->user()?->can('update', $note->staff) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
            'visibility_type' => ['required', 'string', Rule::in(StaffNoteVisibility::values())],
        ];
    }
}
