<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTransportTripLogRequest extends TransportRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'transport_trip_id' => ['required', 'integer', Rule::exists('transport_trips', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'student_id' => ['nullable', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'route_stop_id' => ['nullable', 'integer', Rule::exists('transport_route_stops', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'user_type' => ['required', 'string', Rule::in(['student', 'staff'])],
            'event_type' => ['required', 'string', Rule::in(['boarded', 'dropped', 'missed', 'checked_in', 'checked_out'])],
            'event_time' => ['required', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'marked_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $userType = $this->input('user_type');

            if ($userType === 'student' && ! $this->filled('student_id')) {
                $validator->errors()->add('student_id', 'Student is required when user type is student.');
            }

            if ($userType === 'staff' && ! $this->filled('staff_id')) {
                $validator->errors()->add('staff_id', 'Staff is required when user type is staff.');
            }
        });
    }
}
