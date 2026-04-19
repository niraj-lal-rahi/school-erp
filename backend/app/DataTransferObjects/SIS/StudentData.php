<?php

namespace App\DataTransferObjects\SIS;

use Illuminate\Support\Str;

readonly class StudentData
{
    public function __construct(
        public array $student,
        public array $guardians,
        public array $enrollment,
        public array $admission,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $student = $payload;
        unset($student['guardians'], $student['enrollment'], $student['admission']);
        $student['uuid'] ??= (string) Str::uuid();
        $student['current_status'] = $student['current_status'] ?? $student['status'] ?? 'active';
        $student['status'] = $student['current_status'];
        $student['full_name'] = trim(implode(' ', array_filter([
            $student['first_name'] ?? null,
            $student['middle_name'] ?? null,
            $student['last_name'] ?? null,
        ])));

        return new self(
            student: $student,
            guardians: $payload['guardians'] ?? [],
            enrollment: $payload['enrollment'] ?? [],
            admission: $payload['admission'] ?? [],
        );
    }

    public function studentAttributes(): array
    {
        return $this->student;
    }

    public function guardianPivotData(int $schoolId): array
    {
        return collect($this->guardians)->mapWithKeys(function (array $guardian) use ($schoolId): array {
            return [
                $guardian['id'] => [
                    'school_id' => $schoolId,
                    'relationship' => $guardian['relationship'] ?? null,
                    'relationship_label' => $guardian['relationship_label'] ?? $guardian['relationship'] ?? null,
                    'is_primary' => $guardian['is_primary'] ?? false,
                    'is_emergency_contact' => $guardian['is_emergency_contact'] ?? false,
                    'pickup_authorized' => $guardian['pickup_authorized'] ?? true,
                    'financial_responsibility_percentage' => $guardian['financial_responsibility_percentage'] ?? null,
                    'notes' => $guardian['notes'] ?? null,
                ],
            ];
        })->all();
    }

    public function enrollmentAttributes(int $schoolId): array
    {
        $status = $this->enrollment['status'] ?? 'enrolled';

        if ($status === 'active') {
            $status = 'enrolled';
        }

        return [
            ...$this->enrollment,
            'school_id' => $schoolId,
            'roll_number' => $this->enrollment['roll_number'] ?? $this->enrollment['roll_no'] ?? null,
            'enrollment_date' => $this->enrollment['enrollment_date'] ?? $this->enrollment['joined_on'] ?? now()->toDateString(),
            'is_current' => $this->enrollment['is_current'] ?? true,
            'status' => $status,
        ];
    }

    public function admissionAttributes(int $schoolId): array
    {
        return [
            'school_id' => $schoolId,
            ...$this->admission,
        ];
    }
}
