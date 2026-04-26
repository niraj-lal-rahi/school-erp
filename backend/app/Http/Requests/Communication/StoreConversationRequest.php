<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreConversationRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'conversation_type' => ['required', 'string', Rule::in(['direct', 'group', 'parent_teacher', 'staff', 'class_group'])],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['active', 'archived', 'closed'])],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.participant_type' => ['required', 'string', Rule::in(['student', 'guardian', 'staff', 'user'])],
            'participants.*.participant_id' => ['required', 'integer'],
            'participants.*.is_muted' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('participants', []) as $index => $participant) {
                $type = $participant['participant_type'] ?? null;
                $id = $participant['participant_id'] ?? null;

                $table = match ($type) {
                    'student' => 'students',
                    'guardian' => 'guardians',
                    'staff' => 'staff',
                    'user' => 'users',
                    default => null,
                };

                if ($table === null || ! $id) {
                    continue;
                }

                $exists = \DB::table($table)
                    ->where('school_id', $this->tenantId())
                    ->where('id', $id)
                    ->exists();

                if (! $exists) {
                    $validator->errors()->add("participants.{$index}.participant_id", 'The selected participant is invalid for the current tenant.');
                }
            }
        });
    }
}
