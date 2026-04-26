<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCommunicationGroupRequest extends CommunicationRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $groupId = $this->routeModelId('group');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('communication_groups', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($groupId),
            ],
            'group_type' => ['sometimes', 'required', 'string', Rule::in(['class', 'section', 'staff', 'custom'])],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $storeRequest = new StoreCommunicationGroupRequest();
            $storeRequest->setContainer(app());
            $storeRequest->setRedirector(app('redirect'));
            $storeRequest->merge($this->all());
            $storeRequest->setUserResolver(fn () => $this->user());
            $storeRequest->setRouteResolver(fn () => $this->route());
            $storeRequest->withValidator($validator);
        });
    }
}
