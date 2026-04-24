<?php

namespace App\Http\Requests\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffEmergencyContact;
use Illuminate\Foundation\Http\FormRequest;

class UpsertStaffEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        $contact = $this->route('staffEmergencyContact');

        if ($staff instanceof Staff) {
            return $this->user()?->can('update', $staff) ?? false;
        }

        if ($contact instanceof StaffEmergencyContact) {
            return $this->user()?->can('update', $contact->staff) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
