<?php

namespace App\Http\Requests\Attendance;

use App\Enums\Attendance\AttendanceSessionType;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertStudentAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')],
            'attendance_date' => ['required', 'date'],
            'session_type' => ['required', Rule::enum(AttendanceSessionType::class)],
            'attendance_period_id' => ['nullable', 'integer', Rule::exists('attendance_periods', 'id')],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'teacher_id' => ['nullable', 'integer', Rule::exists('staff', 'id')],
            'status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'locked'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $schoolId = $this->user()?->school_id;

            if (! $schoolId) {
                return;
            }

            if ($this->filled('academic_year_id') && ! AcademicYear::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($this->integer('academic_year_id'))->exists()) {
                $validator->errors()->add('academic_year_id', 'The selected academic year does not belong to this tenant.');
            }

            if ($this->filled('school_class_id') && ! SchoolClass::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($this->integer('school_class_id'))->exists()) {
                $validator->errors()->add('school_class_id', 'The selected class does not belong to this tenant.');
            }

            if ($this->filled('section_id')) {
                $section = Section::withoutGlobalScopes()->where('school_id', $schoolId)->find($this->integer('section_id'));

                if (! $section) {
                    $validator->errors()->add('section_id', 'The selected section does not belong to this tenant.');
                } elseif ($this->filled('school_class_id') && $section->school_class_id !== $this->integer('school_class_id')) {
                    $validator->errors()->add('section_id', 'The selected section does not belong to the selected class.');
                }
            }

            if ($this->input('session_type') === AttendanceSessionType::Period->value && ! $this->filled('attendance_period_id')) {
                $validator->errors()->add('attendance_period_id', 'A period is required for period-wise attendance.');
            }

            if ($this->input('session_type') === AttendanceSessionType::Daily->value && $this->filled('attendance_period_id')) {
                $validator->errors()->add('attendance_period_id', 'Daily attendance sessions cannot include a period.');
            }
        });
    }
}
