<?php

namespace App\Http\Requests\SIS;

use App\Models\Guardian;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $guardian = $this->route('guardian');

        if ($guardian instanceof Guardian) {
            return $this->user()?->can('update', $guardian) ?? false;
        }

        return $this->user()?->can('create', Guardian::class) ?? false;
    }

    public function rules(): array
    {
        $guardian = $this->route('guardian');
        $schoolId = app(TenantContext::class)->id() ?? $this->user()?->school_id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'relationship_type' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('guardians', 'email')->where('school_id', $schoolId)->ignore($guardian?->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'annual_income' => ['nullable', 'numeric', 'min:0'],
            'education' => ['nullable', 'string', 'max:150'],
            'aadhaar_no' => ['nullable', 'string', 'max:32'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'can_receive_sms' => ['nullable', 'boolean'],
            'can_receive_email' => ['nullable', 'boolean'],
            'can_pickup_student' => ['nullable', 'boolean'],
            'address' => ['nullable', 'array'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
