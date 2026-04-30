<?php

namespace App\Http\Requests\Portal;

use App\Models\Portal\PortalProfileAccess;
use App\Models\Portal\PortalUserProfile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class SwitchPortalContextRequest extends PortalRequest
{
    public function rules(): array
    {
        return [
            'active_profile_type' => ['required', Rule::in(['student', 'guardian'])],
            'active_profile_id' => ['required', 'integer'],
            'active_student_id' => ['nullable', 'integer', $this->existsInTenant('students')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $profileType = $this->input('active_profile_type');
            $profileId = $this->integer('active_profile_id');
            $activeStudentId = $this->integer('active_student_id');

            $profileExists = PortalUserProfile::query()
                ->where('user_id', $user?->id)
                ->where('profile_type', $profileType)
                ->where('profile_id', $profileId)
                ->where('status', 'active')
                ->exists();

            if (! $profileExists) {
                $validator->errors()->add('active_profile_id', 'The selected portal profile is not available for this user.');

                return;
            }

            if ($profileType === 'student') {
                if ($activeStudentId && $activeStudentId !== $profileId) {
                    $validator->errors()->add('active_student_id', 'Student context must match the active student profile.');
                }

                if (! $this->userOwnsStudent($profileId)) {
                    $validator->errors()->add('active_profile_id', 'A student can only switch to their own student profile.');
                }

                return;
            }

            if (! $activeStudentId) {
                $validator->errors()->add('active_student_id', 'Guardian context requires a selected child.');

                return;
            }

            $hasAccess = PortalProfileAccess::query()
                ->where('user_id', $user?->id)
                ->where('guardian_id', $profileId)
                ->where('student_id', $activeStudentId)
                ->where('status', 'active')
                ->exists();

            if (! $hasAccess) {
                $validator->errors()->add('active_student_id', 'The selected child is not accessible in the active guardian context.');
            }
        });
    }
}
