<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGroupMemberRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'member_type' => ['required', 'string', Rule::in(['student', 'guardian', 'staff', 'user'])],
            'member_id' => ['required', 'integer'],
            'joined_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $table = match ($this->input('member_type')) {
                'student' => 'students',
                'guardian' => 'guardians',
                'staff' => 'staff',
                'user' => 'users',
                default => null,
            };

            if ($table === null) {
                return;
            }

            $exists = \DB::table($table)
                ->where('school_id', $this->tenantId())
                ->where('id', $this->input('member_id'))
                ->exists();

            if (! $exists) {
                $validator->errors()->add('member_id', 'The selected member is invalid for the current tenant.');
            }
        });
    }
}
