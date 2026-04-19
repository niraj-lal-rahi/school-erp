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
        $student['status'] ??= 'active';

        return new self(
            student: $student,
            guardians: $payload['guardians'],
            enrollment: $payload['enrollment'],
            admission: $payload['admission'],
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
                    'is_primary' => $guardian['is_primary'] ?? false,
                    'is_emergency_contact' => $guardian['is_emergency_contact'] ?? false,
                    'pickup_authorized' => $guardian['pickup_authorized'] ?? true,
                ],
            ];
        })->all();
    }

    public function enrollmentAttributes(int $schoolId): array
    {
        return [
            'school_id' => $schoolId,
            ...$this->enrollment,
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
