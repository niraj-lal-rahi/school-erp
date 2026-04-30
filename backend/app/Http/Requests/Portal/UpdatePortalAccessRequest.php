<?php

namespace App\Http\Requests\Portal;

use App\Models\Portal\PortalProfileAccess;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdatePortalAccessRequest extends PortalRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer', $this->existsInTenant('users')],
            'student_id' => ['sometimes', 'integer', $this->existsInTenant('students')],
            'guardian_id' => ['nullable', 'integer', $this->existsInTenant('guardians')],
            'access_type' => ['sometimes', Rule::in(['self', 'parent', 'guardian'])],
            'can_view_attendance' => ['sometimes', 'boolean'],
            'can_view_fees' => ['sometimes', 'boolean'],
            'can_pay_fees' => ['sometimes', 'boolean'],
            'can_view_results' => ['sometimes', 'boolean'],
            'can_view_documents' => ['sometimes', 'boolean'],
            'can_message_teacher' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $access = $this->route('id') ?? $this->route('portal_access');
            $resolvedAccess = $access instanceof PortalProfileAccess ? $access : null;

            $userId = $this->integer('user_id') ?: $resolvedAccess?->user_id;
            $studentId = $this->integer('student_id') ?: $resolvedAccess?->student_id;
            $guardianId = $this->integer('guardian_id') ?: $resolvedAccess?->guardian_id;
            $accessType = $this->input('access_type', $resolvedAccess?->access_type);

            if (! $userId || ! $studentId || ! $accessType) {
                return;
            }

            if ($accessType === 'self' && ! $this->userOwnsStudent($studentId) && $this->user()?->id === $userId) {
                $validator->errors()->add('student_id', 'Self access can only target the signed-in student record.');
            }

            if (in_array($accessType, ['parent', 'guardian'], true)) {
                if (! $guardianId) {
                    $validator->errors()->add('guardian_id', 'Guardian access requires a guardian profile.');
                } elseif (! $this->guardianOwnsStudent($guardianId, $studentId)) {
                    $validator->errors()->add('guardian_id', 'The selected guardian is not linked to the selected student.');
                }
            }

            $duplicateQuery = PortalProfileAccess::query()
                ->where('user_id', $userId)
                ->where('student_id', $studentId)
                ->where('access_type', $accessType);

            if ($resolvedAccess) {
                $duplicateQuery->whereKeyNot($resolvedAccess->id);
            }

            if ($duplicateQuery->exists()) {
                $validator->errors()->add('access_type', 'This portal access mapping already exists for the user and student.');
            }
        });
    }
}
