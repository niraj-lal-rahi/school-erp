<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCommunicationGroupRequest extends CommunicationRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('communication_groups', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'group_type' => ['required', 'string', Rule::in(['class', 'section', 'staff', 'custom'])],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $groupType = $this->input('group_type');

            if ($groupType === 'class' && ! $this->filled('class_id')) {
                $validator->errors()->add('class_id', 'The class_id field is required for class groups.');
            }

            if ($groupType === 'section') {
                if (! $this->filled('class_id')) {
                    $validator->errors()->add('class_id', 'The class_id field is required for section groups.');
                }

                if (! $this->filled('section_id')) {
                    $validator->errors()->add('section_id', 'The section_id field is required for section groups.');
                }
            }

            if ($this->filled('section_id') && $this->filled('class_id')) {
                $sectionBelongsToClass = \DB::table('sections')
                    ->where('school_id', $this->tenantId())
                    ->where('id', $this->input('section_id'))
                    ->where('class_id', $this->input('class_id'))
                    ->exists();

                if (! $sectionBelongsToClass) {
                    $validator->errors()->add('section_id', 'The selected section does not belong to the selected class.');
                }
            }
        });
    }
}
